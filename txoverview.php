<?php

namespace RKWhmcsUtils;

require_once(__DIR__ . '/vendor/autoload.php');

$config = Config::getInstance();

$db = WhmcsDb::buildInstance();

$invoices = $db->getInvoices();
krsort($invoices);

$invoiceItems = $db->getInvoiceItemsByInvoiceId();

$users = $db->getUsers();

$affiliates = $db->getAffiliates();

$affiliateAccounts = $db->getAffiliateAccounts();

$findAffiliateForInvoicedUserId = function ($userId) use ($affiliates, $affiliateAccounts) {
    // See if the user ID matches one of our affiliates' _linked_ accounts
    foreach ($affiliateAccounts as $account) {
        if ($account['userid'] === $userId && isset($affiliates[$account['affiliateid']])) {
            return $affiliates[$account['affiliateid']];
        }
    }
    // Alternatively, see if the user ID matches our affiliates themselves
    foreach ($affiliates as $affiliate) {
        if ($affiliate['userid'] === $userId) {
            return $affiliate;
        }
    }
    return null;
};

include(__DIR__ . '/inc/00-head.php');

?>
<style>
    .invoice-status {
        color: #f00
    }

    .invoice-status.paid {
        color: chartreuse;
    }
</style>
</head>
<body>
<table>
    <thead>
    <th>Invoice ID</th>
    <th>Created At</th>
    <th>Status</th>
    <th>Payment method</th>
    <th>Subtotal</th>
    <th>+ Tax</th>
    <th>- Credit</th>
    <th>Total</th>
    <th>PDF</th>
    <th>User ID</th>
    <th>Affiliate</th>
    <th>Commission</th>
    </thead>
    <tbody>
    <?php foreach ($invoices as $invoiceId => $invoice):
        $isPaid = $invoice['status'] === 'Paid';
        $tax = (float)$invoice['tax'];
        $taxRate = (float)$invoice['taxrate'];
        $taxDisplay = $tax > 0
            ? "($taxRate%) " . str_pad(number_format($tax, 2), 6, ' ', STR_PAD_LEFT)
            : '';
        $credit = (float)$invoice['credit'];
        $creditDisplay = $credit > 0
            ? "-" . str_pad(number_format($credit, 2), 6, ' ', STR_PAD_LEFT)
            : '';
        $affiliate = $findAffiliateForInvoicedUserId($invoice['userid']);
        $affiliateDisplay = $affiliate ? $affiliate['companyname'] : '';
        $commissionDisplay = '';
        if ($affiliate && $isPaid) {
            if ($affiliate['paytype'] === 'percentage') {
                $perc = (float)$affiliate['payamount'];
                $commissionAmount = (float)$invoice['total'] * ($perc / 100);
                $commissionDisplay = str_pad("(" . $affiliate['payamount'] . "%)", 6, ' ', STR_PAD_LEFT) . " ";
                $commissionDisplay .= str_pad(number_format($commissionAmount, 2), 6, ' ', STR_PAD_LEFT);
            } else {
                $commissionDisplay = 'UNSUPPORTED PAYTYPE';
            }
        }

        ?>
        <tr>
            <td><a target="_blank"
                   href="<?= $config->whmcsAdminRoot ?>invoices.php?action=edit&id=<?= $invoiceId ?>"><?= $invoiceId ?>
            </td>
            <td><?= $invoice['date'] ?></td>
            <td class="invoice-status <?= $isPaid ? 'paid' : '' ?>">
                <?= $invoice['status'] ?>
                <?php if ($isPaid):
                    $dtPaid = new \DateTime($invoice['datepaid']);
                    ?>
                    <span class="paid-at" title="<?= $invoice['datepaid'] ?>"> (<?= $dtPaid->format('Y-m-d') ?>)
              </span>
                <?php endif; ?>
            </td>
            <td><?= $invoice['paymentmethod'] ?></td>
            <td class="right-align"><?= $invoice['subtotal'] ?></td>
            <td><?= $taxDisplay ?></td>
            <td class="right-align"><?= $creditDisplay ?></td>
            <td class="right-align"><?= $invoice['total'] ?></td>
            <td><a target="_blank"
                   href="<?= $config->whmcsRoot ?>dl.php?type=i&id=<?= $invoiceId ?>&language=english">PDF</a></td>
            <td><?= $invoice['userid'] ?></td>
            <td class="thin"><?= $affiliateDisplay ?></td>
            <td class="right-align"><?= $commissionDisplay ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
