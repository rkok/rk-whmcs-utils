<?php

namespace RKWhmcsUtils;

require_once(__DIR__ . '/vendor/autoload.php');

const VAT_PERC = 20;

$whmcsDb = WhmcsDb::buildInstance();

$clients = $whmcsDb->getActiveUsersList();

$domains = $whmcsDb->getActiveDomainsListByUserId();

$services = $whmcsDb->getActiveServicesListByUserId();

$billingCyclesToMonths = [
    'Monthly' => 1,
    'Quarterly' => 3,
    'Semi-Annually' => 6,
    'Annually' => 12
];

$calcVatComponent = function ($totalInclVat, $vatPerc) {
    $totalExclVat = $totalInclVat / ((100 + $vatPerc) / 100);
    return $totalInclVat - $totalExclVat;
};

// Join all into a fat list
foreach ($clients as $cKey => $client) {
    $cId = $client['client_id'];

    $affiliatePerc = 0;
    if ($client['aff_paytype'] === 'percentage') {
        $affiliatePerc = (float)$client['aff_payamount'];
    }

    $billables = [];

    if (isset($domains[$cId])) {
        $billables = array_map(function ($domain) use ($affiliatePerc, $calcVatComponent) {
            $monthlyAmount = 0;
            if ((float)$domain['recurringamount'] > 0) {
                $monthlyAmount = (float)$domain['recurringamount'] / ((int)$domain['registrationperiod'] * 12);
            }
            return [
                'type' => 'Domain',
                'domain' => $domain['domain'],
                'monthly_amount' => $monthlyAmount,
                'monthly_aff_fee' => $monthlyAmount * ($affiliatePerc / 100),
                'monthly_vat_fee' => $calcVatComponent($monthlyAmount, VAT_PERC),
                'payment_method' => $domain['paymentmethod'],
                'notes' => $domain['notes']
            ];
        }, $domains[$cId]);
    }

    if (isset($services[$cId])) {
        $billables = array_merge($billables,
            array_map(function ($service) use ($affiliatePerc, $billingCyclesToMonths, $calcVatComponent) {
                $monthlyAmount = 0;
                if ((float)$service['amount'] > 0) {
                    if ($months = $billingCyclesToMonths[$service['billingcycle']]) {
                        $monthlyAmount = (float)$service['amount'] / $months;
                    } else {
                        $monthlyAmount = -1; // Error
                    }
                }
                return [
                    'type' => 'Hosting',
                    'domain' => $service['domain'],
                    'monthly_amount' => $monthlyAmount,
                    'monthly_aff_fee' => $monthlyAmount * ($affiliatePerc / 100),
                    'monthly_vat_fee' => $calcVatComponent($monthlyAmount, VAT_PERC),
                    'payment_method' => $service['paymentmethod'],
                    'notes' => $service['notes']
                ];
            }, $services[$cId]));
    }

    // Sort by domain name
    usort($billables, function ($a, $b) {
        return $a['domain'] > $b['domain'];
    });

    $clients[$cKey]['billables'] = $billables;
}

?>
<!DOCTYPE html>
<head>
  <link rel="stylesheet" href="css/base.css" />
  <style>
      th.notes, td.notes {
          max-width: 200px;
          word-wrap: break-word;
          white-space: normal;
          overflow-wrap: break-word;
      }

      tr.grandtotal td {
          font-weight: bold;
      }
  </style>
</head>
<body>
<table>
  <thead>
    <tr>
      <th>UID</th>
      <th>Email</th>
      <th>CID</th>
      <th>Name</th>
      <th>Affiliate</th>
      <th colspan="7">Billables</th>
    </tr>
    <tr>
      <th colspan="5"></th>
      <th>Domain</th>
      <th>Type</th>
      <th class="thin2">Amt./mo (gross)</th>
      <th class="thin2">Aff. fee / mo</th>
      <th class="thin2">VAT / mo</th>
      <th class="thin">Payment method</th>
      <th class="notes">Notes</th>
    </tr>
  </thead>
  <tbody>
      <?php
      $grandTotal = 0;
      $grandTotalAff = 0;
      $grandTotalVat = 0;

      foreach ($clients as $client): ?>
        <tr>
          <td><?= $client['user_id'] ?></td>
          <td><?= $client['email'] ?></td>
          <td><?= $client['client_id'] ?></td>
          <td><?= $client['firstname'] ?> <?= $client['lastname'] ?></td>
          <td class="affiliate"><?= $client['aff_company'] ?: '' ?></td>
        </tr>
          <?php foreach ($client['billables'] as $billable): ?>
          <tr>
            <td colspan="5"></td>
            <td><?= $billable['domain'] ?></td>
            <td><?= $billable['type'] ?></td>
            <td><?= str_pad(number_format($billable['monthly_amount'], 2), 8, ' ', STR_PAD_LEFT) ?></td>
            <td><?=
                $billable['monthly_aff_fee']
                    ? str_pad(number_format($billable['monthly_aff_fee'], 2), 8, ' ', STR_PAD_LEFT)
                    : ''
                ?></td>
            <td><?= str_pad(number_format($billable['monthly_vat_fee'], 2), 8, ' ', STR_PAD_LEFT) ?></td>
            <td><?= $billable['payment_method'] ?></td>
            <td class="notes"><?= $billable['notes'] ?></td>
          </tr>
              <?php

              $grandTotal += $billable['monthly_amount'];
              $grandTotalAff += $billable['monthly_aff_fee'];
              $grandTotalVat += $billable['monthly_vat_fee'];
          endforeach;

          ?>
      <?php endforeach; ?>
    <tr>
      <td colspan="13">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="8"></td>
      <td>- Aff</td>
      <td>- Aff + VAT</td>
    </tr>
    <tr class="grandtotal">
      <td colspan="6"></td>
      <td>Grand Total:</td>
      <td><?= str_pad(number_format($grandTotal, 2), 8, ' ', STR_PAD_LEFT) ?></td>
      <td><?= str_pad(number_format($grandTotal - $grandTotalAff, 2), 8, ' ', STR_PAD_LEFT) ?></td>
      <td><?= str_pad(number_format($grandTotal - $grandTotalAff - $grandTotalVat, 2), 8, ' ', STR_PAD_LEFT) ?></td>
    </tr>
  </tbody>
</table>
</body>
