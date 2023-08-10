<?php

namespace RKWhmcsUtils;

use Ramsey\Uuid\Uuid;
use RKWhmcsUtils\Models\GnucashTransactionLine;
use RKWhmcsUtils\Models\WhmcsInvoiceItem;

require_once(__DIR__ . '/../vendor/autoload.php');

$db = WhmcsDb::buildInstance();

$repo = new WhmcsRepository($db);

$datePaidFrom = new \DateTime('1970-01-01');
try {
    if (preg_match("/^\d{4}-\d{2}-\d{2}$/", @$_GET['datePaidFrom'])) {
        $datePaidFrom = new \DateTime($_GET['datePaidFrom']);
    }
} catch(\Exception $e) {
    Util::exitWithJsonError("Invalid datePaidFrom", 400);
}

$results = [];

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

    $results[] = (new GnucashTransactionLine())
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

        $results[] = (new GnucashTransactionLine())
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
        $results[] = (new GnucashTransactionLine())
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

        $results[] = (new GnucashTransactionLine())
            ->setDate($date)
            ->setId($creditUuid)
            ->setDescription("Credit payment - $description")
            ->setFullAccountName("Accounts Receivable")
            ->setAmount(-$credit)
            ->toArray();
        $results[] = (new GnucashTransactionLine())
            ->setDate($date)
            ->setId($creditUuid)
            ->setDescription("Credit payment - $description")
            ->setFullAccountName("Credit Payable - {$client->getFullNameFormatted()}")
            ->setAmount($credit)
            ->toArray();
    }
}

$csv = Util::makeCsv([GnucashTransactionLine::GNUCASH_CSV_COLS, ...$results]);

$exportName = "whmcs-txns-" . date('Ymd-His') . '.csv';

header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=$exportName");
exit($csv);
