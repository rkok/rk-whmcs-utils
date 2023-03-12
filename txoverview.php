<?php

namespace RKWhmcsUtils;

require_once(__DIR__ . '/vendor/autoload.php');

$config = Config::getInstance();

$db = WhmcsDb::buildInstance();

$invoices = $db->getInvoices();

$clients = $db->getClients();

$affiliates = $db->getAffiliates();

$clientAffiliateIds = $db->getClientAffiliateIds();

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
    <th>Client</th>
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
        $clientId = null;

        if (isset($clients[$invoice->getClientId()])) {
            $clientId = $invoice->getClientId();
            $client = $clients[$clientId];
            $clientDisplay = $client->getDisplayName();
        } else {
            $clientDisplay = 'Error matching client';
        }

        $affiliate = null;
        if (isset($clientAffiliateIds[$clientId])) {
            $affiliate = $affiliates[$clientAffiliateIds[$clientId]];
        }

        $commissionDisplay = '';
        if ($affiliate && $invoice->isPaid()) {
            try {
                $commissionAmount = $affiliate->calculateCommission($invoice->getSubTotal());
                $commissionDisplay = str_pad("(" . $affiliate->getPayAmount() . "%)", 6, ' ', STR_PAD_LEFT) . " ";
                $commissionDisplay .= str_pad(number_format($commissionAmount, 2), 6, ' ', STR_PAD_LEFT);
            } catch (\Exception $e) {
                $commissionDisplay = 'ERROR';
            }
        }

        ?>
        <tr>
            <td><a target="_blank"
                   href="<?= $config->whmcsAdminRoot ?>invoices.php?action=edit&id=<?= $invoiceId ?>"><?= $invoiceId ?>
            </td>
            <td><?= $invoice->getDate()->format('Y-m-d') ?></td>
            <td class="thin trimoverflow"><a target="_blank"
                                             href="<?= $config->whmcsAdminRoot ?>clientssummary.php?userid=<?= $clientId ?>"><?= $clientDisplay ?></a>
            </td>
            <td class="invoice-status <?= $invoice->isPaid() ? 'paid' : '' ?>">
                <?= $invoice->getStatus() ?>
                <?php if ($invoice->isPaid()):
                    $dtPaid = $invoice->getDatePaid();
                    ?>
                    <span class="paid-at"
                          title="<?= $dtPaid->format('Y-m-d H:i:s') ?>"> (<?= $dtPaid->format('Y-m-d') ?>)
              </span>
                <?php endif; ?>
            </td>
            <td><?= $invoice->getPaymentMethod() ?></td>
            <td class="right-align"><?= number_format($invoice->getSubTotal(), 2) ?></td>
            <td><?= $invoice->getTax() > 0
                    ? "({$invoice->getTaxRate()}%) " . str_pad(number_format($invoice->getTax(), 2), 6, ' ', STR_PAD_LEFT)
                    : ''; ?></td>
            <td class="right-align"><?= $invoice->getCredit() > 0
                    ? "-" . str_pad(number_format($invoice->getCredit(), 2), 6, ' ', STR_PAD_LEFT)
                    : ''; ?></td>
            <td class="right-align"><?= number_format($invoice->getTotal(), 2) ?></td>
            <td><a target="_blank"
                   href="<?= $config->whmcsRoot ?>dl.php?type=i&id=<?= $invoiceId ?>&language=english">PDF</a>
            </td>
            <td><?= $invoice->getUserId() ?></td>
            <td class="thin"><?= $affiliate ? $affiliate->getDisplayName() : '' ?></td>
            <td class="right-align"><?= $commissionDisplay ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
