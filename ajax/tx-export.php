<?php

namespace RKWhmcsUtils;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

require_once(__DIR__ . '/../vendor/autoload.php');

$repo = new WhmcsRepository(WhmcsDb::buildInstance());

$config = Config::getInstance();

$excel = new Spreadsheet();

$worksheet = $excel->getActiveSheet();

$alphabet = range('A', 'Z');

$columns = [
    'Invoice ID',
    'Created On',
    'Client ID',
    'Client',
    'Status',
    'Paid On',
    'Payment method',
    'Subtotal',
    '+ Tax',
    '- Credit',
    'Total',
    'PDF',
    'Affiliate ID',
    'Affiliate',
    'Commission',
    'Commission percentage'
];

// Write column headings
foreach ($alphabet as $i => $colId) {
    if (!isset($columns[$i])) break;
    $worksheet->setCellValue($colId . "1", $columns[$i]);
    $worksheet->getCell($colId . "1")->getStyle()->getFont()->setBold(true);
}

foreach ($repo->getTransactionList() as $i => $transaction) {
    $invoice = $transaction->getInvoice();
    $client = $transaction->getClient();
    $affiliate = $transaction->getAffiliate();

    $rowData = [
        $invoice->getId() . ' ', // Add space, else phpSpreadsheet won't add hyperlink for some reason
        $invoice->getDate()->format('Y-m-d'),
        $client ? $client->getId() . ' ' : '', // Add space, else phpSpreadsheet won't add hyperlink for some reason
        $client ? $client->getDisplayName() : '',
        $invoice->getStatus(),
        $invoice->getStatus() === 'Paid' ? $invoice->getDatePaid() : '',
        $invoice->getPaymentMethod(),
        $invoice->getSubTotal(),
        $invoice->getTax(),
        $invoice->getCredit(),
        $invoice->getTotal(),
        'PDF',
        $affiliate ? $affiliate->getId() : '',
        $affiliate ? $affiliate->getDisplayName() : '',
        $affiliate ? $transaction->calculateAffiliateCommission() : '',
        $affiliate ? $affiliate->getPayAmount() : '',
    ];

    $rowId = $i + 2;

    foreach ($alphabet as $j => $colId) {
        if (!isset($rowData[$j])) break;
        $worksheet->setCellValue("$colId$rowId", $rowData[$j]);
    }

    $worksheet->getCell("A$rowId")->getHyperlink()->setUrl($config->whmcsAdminRoot . $invoice->generateInvoiceEditUrlPath());
    $worksheet->getCell("C$rowId")->getHyperlink()->setUrl($config->whmcsAdminRoot . $client->generateClientViewUrlPath());
    $worksheet->getCell("K$rowId")->setValue('PDF')->getHyperlink()->setUrl($config->whmcsRoot . $invoice->generateInvoiceDownloadUrlPath());
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
