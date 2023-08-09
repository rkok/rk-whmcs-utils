<?php

namespace RKWhmcsUtils;

use Ramsey\Uuid\Uuid;

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

function formatAmount($amount) {
    return number_format($amount, 2, ',', '');
}

// Debit accounts that we generate CSV rows for
$_accountsReceivable = [];
$_commissionExpenses = [];


foreach ($repo->getTransactionList() as $i => $transaction) {
    $invoice = $transaction->getInvoice();
    if ($invoice->getStatus() !== 'Paid' || count($invoice->getItems()) === 0) {
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
    $description = "{$invoice->getId()} - {$client->getDisplayName()}";
    if ($companyName = $client->getCompanyName()) {
        $description .= " - $companyName";
    }
    if ($affiliate) {
        $description .= " (Aff. {$affiliate->getDisplayName()})";
    }

    $totalExCredit = $invoice->getSubTotal() + $invoice->getTax();

    if ($totalExCredit === 0.0) {
        continue;
    }

    $_accountsReceivable[] = [
        $date,
        $txUuid,
        '',
        $description,
        '',
        '', // Commodity/Currency (TODO)
        '', // Void Reason,
        '', // Action
        '', // Memo
        // Full Account Name
        // Use invoiceitems.type (Hosting/DomainRegister/...) as placeholder
        "Accounts Receivable",
        '', // Account Name (unused)
        '', // Amount With Sym
        formatAmount($totalExCredit), // Amount Num.
        '', // Value With Sym
        formatAmount($totalExCredit), // Value Num.
        'c', // Reconciled (n = new, c = cleared)
        '', // Reconcile Date
        1, // Rate/Price
    ];

    // Sub-rows - income sides
    foreach ($invoice->getItems() as $item) {
        if ($item->getAmount() === 0) {
            continue;
        }

        $_accountsReceivable[] = [
            $date,
            $txUuid,
            '',
            $description,
            $item->getNotes(),
            '', // Commodity/Currency (TODO)
            '', // Void Reason,
            '', // Action
            implode(" - ", explode("\n", $item->getDescription())), // Memo
            // Full Account Name
            // Use invoiceitems.type (Hosting/DomainRegister/...) as placeholder
            $item->getType(),
            '', // Account Name (unused)
            '', // Amount With Sym
            formatAmount(-$item->getAmount()), // Amount Num.
            '', // Value With Sym
            formatAmount(-$item->getAmount()), // Value Num.
            'n', // Reconciled (n = new, c = cleared)
            '', // Reconcile Date
            1, // Rate/Price
        ];
    }

    // Sub-row for tax
    if (($tax = $invoice->getTax()) > 0) {
        $_accountsReceivable[] = [
            $date,
            $txUuid,
            '',
            $description,
            '',
            '', // Commodity/Currency (TODO)
            '', // Void Reason,
            '', // Action
            "{$invoice->getTaxRate()}% Tax", // Memo
            // Full Account Name
            // Use invoiceitems.type (Hosting/DomainRegister/...) as placeholder
            "Tax",
            '', // Account Name (unused)
            '', // Amount With Sym
            formatAmount(-$tax), // Amount Num.
            '', // Value With Sym
            formatAmount(-$tax), // Value Num.
            'n', // Reconciled (n = new, c = cleared)
            '', // Reconcile Date
            1, // Rate/Price
        ];
    }

    // TODO: Credit deduction
    // TODO: Affiliate commission
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

$csv = makeCsv([$columns, ...$_accountsReceivable]);

$exportName = "whmcs-txns-" . date('Ymd-His') . '.csv';

header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=$exportName");
exit($csv);
