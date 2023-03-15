<?php

namespace RKWhmcsUtils;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

require_once(__DIR__ . '/../vendor/autoload.php');

$repo = new WhmcsRepository(WhmcsDb::buildInstance());

$excel = new Spreadsheet();

$worksheet = $excel->getActiveSheet();

$columns = [
    'Invoice ID',
    'Created At',
    'Client ID',
    'Client',
    'Status',
    'Payment method',
    'Subtotal',
    '+ Tax',
    '- Credit',
    'Total',
    'Affiliate ID',
    'Affiliate',
    'Commission',
    'Commission percentage'
];

// Write column headings
foreach (range('A', 'Z') as $i => $colId) {
    if (!isset($columns[$i])) break;
    $worksheet->setCellValue($colId . "1", $columns[$i]);
}

foreach ($repo->getTransactionList() as $i => $transaction) {
    $invoice = $transaction->getInvoice();
    $client = $transaction->getClient();
    $affiliate = $transaction->getAffiliate();

    $rowData = [
        $invoice->getId(),
        $invoice->getDate()->format('Y-m-d'),
        $client ? $client->getId() : '',
        $client ? $client->getDisplayName() : '',
        $invoice->getStatus(),
        $invoice->getPaymentMethod(),
        $invoice->getSubTotal(),
        $invoice->getTax(),
        $invoice->getCredit(),
        $invoice->getTotal(),
        $affiliate ? $affiliate->getId() : '',
        $affiliate ? $affiliate->getDisplayName() : '',
        $affiliate ? $transaction->calculateAffiliateCommission() : '',
        $affiliate ? $affiliate->getPayAmount() : ''
    ];

    $rowId = $i + 2;

    foreach (range('A', 'Z') as $j => $colId) {
        if (!isset($rowData[$j])) break;
        $worksheet->setCellValue("$colId$rowId", $rowData[$j]);
    }
}

// Auto-size all columns
foreach ($worksheet->getColumnIterator() as $column) {
    $worksheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
}

$writer = IOFactory::createWriter($excel, 'Xlsx');

$exportName = "whmcs-txns-" . date('Ymd-His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $exportName . '"');
$writer->save('php://output');
