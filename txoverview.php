<?php

namespace RKWhmcsUtils;

require_once(__DIR__ . '/vendor/autoload.php');

$db = WhmcsDb::buildInstance();

$invoices = $db->getInvoices();
krsort($invoices);

$invoiceItems = $db->getInvoiceItemsByInvoiceId();

$users = $db->getUsers();

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
              : ''
          ?>
        <tr>
          <td><a target="_blank" href="../invoices.php?action=edit&id=<?=$invoiceId?>"><?= $invoiceId ?></td></td>
          <td><?= $invoice['date'] ?></td>
          <td class="no-whitespace invoice-status <?= $isPaid ? 'paid' : '' ?>">
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
          <td><a target="_blank" href="/dl.php?type=i&id=<?= $invoiceId ?>&language=english">PDF</a></td>
        </tr>
      <?php endforeach; ?>
  </tbody>
</table>
</body>
</html>
