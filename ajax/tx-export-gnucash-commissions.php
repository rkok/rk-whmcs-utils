<?php

namespace RKWhmcsUtils;

use Ramsey\Uuid\Uuid;
use RKWhmcsUtils\Models\GnucashTransactionLine;
use RKWhmcsUtils\Models\WhmcsCommissionEntry;
use RKWhmcsUtils\Models\WhmcsInvoiceItem;
use RKWhmcsUtils\Models\WhmcsTransaction;

require_once(__DIR__ . '/../vendor/autoload.php');

$db = WhmcsDb::buildInstance();

$repo = new WhmcsRepository($db);

// TODO: if performance becomes a big deal,
// use something more optimized for finding matching invoices
$transactions = $repo->getTransactionList();

/** @var WhmcsTransaction[][] $txnIndex */
$txnIndex = [];
foreach ($transactions as $transaction) {
    if (!($aff = $transaction->getAffiliate())) {
        continue;
    }
    $txnIndex[$aff->getId()] = $txnIndex[$aff->getId()] ?? [];
    $txnIndex[$aff->getId()][] = $transaction;
}

$commissionEntries = $db->getCommissionEntriesByAffiliateId();

$affiliates = $db->getAffiliatesIndexedById();

$dateFrom = new \DateTime('1970-01-01');
try {
    if (preg_match("/^\d{4}-\d{2}-\d{2}$/", @$_GET['dateFrom'])) {
        $dateFrom = new \DateTime($_GET['dateFrom']);
    }
} catch (\Exception $e) {
    Util::exitWithJsonError("Invalid dateFrom", 400);
}

$results = [];

foreach ($commissionEntries as $commissionEntry) {
    if ($commissionEntry->getDate() < $dateFrom) {
        continue;
    }

    $affId = $commissionEntry->getAffiliateId();
    if (!isset($txnIndex[$affId])) {
        Util::exitWithJsonError("Couldn't find any transactions for affiliate $affId");
    } else if (!($aff = $affiliates[$affId])) {
        Util::exitWithJsonError("Couldn't find affiliate with ID $affId");
    }

    // Find transaction matching this commission entry
    $transaction = null;
    foreach ($txnIndex[$affId] as $txn) {
        if ($commissionEntry->matchesTransaction($txn)) {
            $transaction = $txn;
            break;
        }
    }

    $description = "Commission";
    if ($d = $commissionEntry->getDescription()) {
        $description .= " - $d";
    }
    if ($transaction) {
        $inv = $transaction->getInvoice();
        $client = $transaction->getClient();
        $description .= " - Invoice #{$inv->getId()} - {$client->getFullNameFormatted()} - {$client->getCompanyName()}";
    } else {
        $description .= " - No matching invoice";
    }

    $date = $commissionEntry->getDate()->format('Y-m-d');
    $commUuid = (Uuid::uuid4())->toString();

    $results[] = (new GnucashTransactionLine())
        ->setDate($date)
        ->setId($commUuid)
        ->setDescription($description)
        ->setFullAccountName("Sales Commissions")
        ->setAmount($commissionEntry->getAmount())
        ->toArray();
    $results[] = (new GnucashTransactionLine())
        ->setDate($date)
        ->setId($commUuid)
        ->setDescription($description)
        ->setFullAccountName("Commissions Payable - {$aff->getFullNameFormatted()}")
        ->setAmount(-$commissionEntry->getAmount())
        ->toArray();
}

$csv = Util::makeCsv([GnucashTransactionLine::GNUCASH_CSV_COLS, ...$results]);

$exportName = "whmcs-comm-" . date('Ymd-His') . '.csv';

header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=$exportName");
exit($csv);
