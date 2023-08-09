<?php

namespace RKWhmcsUtils;

use Ramsey\Uuid\Uuid;
use RKWhmcsUtils\Models\GnucashTransactionLine;
use RKWhmcsUtils\Models\WhmcsInvoiceItem;

require_once(__DIR__ . '/../vendor/autoload.php');

$repo = new WhmcsRepository(WhmcsDb::buildInstance());

function makeCsv($rows)
{
    $fh = fopen('php://output', 'w');
    ob_start();
    foreach ($rows as $row) {
        fputcsv($fh, $row);
    }
    return ob_get_clean();
}

function exitWithError($message, $code = 500) {
    http_response_code($code);
    header('content-type: application/json');
    echo json_encode(['error' => $message]);
    exit();
}

$datePaidFrom = new \DateTime('1970-01-01');
try {
    if (preg_match("/^\d{4}-\d{2}-\d{2}$/", @$_GET['datePaidFrom'])) {
        $datePaidFrom = new \DateTime($_GET['datePaidFrom']);
    }
} catch(\Exception $e) {
    exitWithError("Invalid datePaidFrom", 400);
}

$transactions = [];

foreach ($repo->getTransactionList() as $transaction) {
    $invoice = $transaction->getInvoice();
    if (
        $invoice->getStatus() !== 'Paid'
        || count($invoice->getItems()) === 0
        || $invoice->getDatePaid() < $datePaidFrom
    ) {
        continue;
    }

    $totalExCredit = $invoice->getSubTotal() + $invoice->getTax();
    if ($totalExCredit === 0.0) {
        continue;
    }

    // Filter out "meta-invoices" that only contain references to other invoices
    // This happens when a client pays multiple invoices at once.
    // We don't need these, as we generate lines for the sub-invoices already,
    // which are more detailed (= better).
    $isMetaInvoice = Util::arrayEvery($invoice->getItems(), function(WhmcsInvoiceItem $item) {
        return $item->getType() === 'Invoice';
    });
    if ($isMetaInvoice) {
        continue;
    }

    $client = $transaction->getClient();
    $affiliate = $transaction->getAffiliate();

    $date = $invoice->getDate()->format('Y-m-d');
    $txUuid = (Uuid::uuid4())->toString();

    // Main row (accounts receivable side)
    $description = "{$invoice->getId()} - {$client->getFullNameFormatted()}";
    if ($companyName = $client->getCompanyName()) {
        $description .= " - $companyName";
    }
    if ($affiliate) {
        $description .= " (Aff. {$affiliate->getDisplayName()})";
    }

    $transactions[] = (new GnucashTransactionLine())
        ->setDate($date)
        ->setId($txUuid)
        ->setDescription($description)
        ->setMemo($client->getEmail())
        ->setFullAccountName("Accounts Receivable")
        ->setAmount($totalExCredit)
        ->toArray();

    // Sub-rows - income sides
    foreach ($invoice->getItems() as $item) {
        if ($item->getAmount() === 0.0) {
            continue;
        }

        $amount = $item->getAmount();
        if ($invoice->isVatInclusive()) {
            // For vat-inclusive invoices:
            // Subtract VAT evenly from all invoice rows,
            // so the total with VAT adds up correctly
            $amount = $amount / (1 + ($invoice->getTaxRate() / 100));
        }

        $transactions[] = (new GnucashTransactionLine())
            ->setDate($date)
            ->setId($txUuid)
            ->setDescription($description)
            ->setNotes($item->getNotes())
            ->setMemo(implode(" - ", explode("\n", $item->getDescription())))
            // Use invoiceitems.type (Hosting/DomainRegister/...) as placeholder
            ->setFullAccountName($item->getType())
            ->setAmount(-$amount)
            ->toArray();
    }

    // Sub-row for tax
    if (($tax = $invoice->getTax()) > 0) {
        $transactions[] = (new GnucashTransactionLine())
            ->setDate($date)
            ->setId($txUuid)
            ->setDescription($description)
            ->setMemo("{$invoice->getTaxRate()}% Tax")
            ->setFullAccountName('Tax')
            ->setAmount(-$tax)
            ->toArray();
    }

    if (($credit = $invoice->getCredit()) > 0) {
        $creditUuid = (Uuid::uuid4())->toString();

        $transactions[] = (new GnucashTransactionLine())
            ->setDate($date)
            ->setId($creditUuid)
            ->setDescription("Credit payment - $description")
            ->setFullAccountName("Accounts Receivable")
            ->setAmount(-$credit)
            ->toArray();
        $transactions[] = (new GnucashTransactionLine())
            ->setDate($date)
            ->setId($creditUuid)
            ->setDescription("Credit payment - $description")
            ->setFullAccountName("Credit Payable - {$client->getFullNameFormatted()}")
            ->setAmount($credit)
            ->toArray();
    }

    try {
        $commission = $transaction->calculateAffiliateCommission();
    } catch(\Exception $e) {
        exitWithError("Invoice {$invoice->getId()}: " . $e->getMessage());
    }

    if ($affiliate && !empty($commission) && $commission > 0) {
        $commissionUuid = (Uuid::uuid4())->toString();
        $commissionDescription = "Commission - $description";

        $transactions[] = (new GnucashTransactionLine())
            ->setDate($date)
            ->setId($commissionUuid)
            ->setDescription($commissionDescription)
            ->setFullAccountName("Sales Commissions")
            ->setAmount($commission)
            ->toArray();
        $transactions[] = (new GnucashTransactionLine())
            ->setDate($date)
            ->setId($commissionUuid)
            ->setDescription($commissionDescription)
            ->setFullAccountName("Commissions Payable - {$affiliate->getFullNameFormatted()}")
            ->setAmount(-$commission)
            ->toArray();
    }
}

$columns = [
    'Date',
    'Transaction ID',
    'Number',
    'Description',
    'Notes',
    'Commodity/Currency',
    'Void Reason',
    'Action',
    'Memo',
    'Full Account Name',
    'Account Name',
    'Amount With Sym',
    'Amount Num.',
    'Value With Sym',
    'Value Num.',
    'Reconcile',
    'Reconcile Date',
    'Rate/Price'
];

$csv = makeCsv([$columns, ...$transactions]);

$exportName = "whmcs-txns-" . date('Ymd-His') . '.csv';

header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=$exportName");
exit($csv);
