<?php

namespace RKWhmcsUtils;

require_once(__DIR__ . '/vendor/autoload.php');

$whmcsDomains = (WhmcsDb::buildInstance())->getActiveDomains();

$enom = Enom::buildInstance();
$enomDomains = [];
$enomTldCounts = [];

try {
    $enomDomainsRaw = $enom->call('GetDomains', ['Display' => 100]);
    foreach ($enomDomainsRaw['domain-list']['domain'] as $domain) {
        /*
         {
            "DomainNameID": "381598879",
            "sld": "example",
            "tld": "com",
            "ns-status": "NA",
            "expiration-date": "6/1/2023",
            "auto-renew": "Yes",
            "wppsstatus": "n/a",
            "RRProcessor": "E"
        },
         */
        $domainName = $domain['sld'] . "." . $domain['tld'];
        $enomDomains[$domainName] = [
            'expirationDate' => $domain['expiration-date'],
            'autoRenew' => $domain['auto-renew']
        ];

        $enomTldCounts[$domain['tld']] = (@$enomTldCounts[$domain['tld']] ?: 0) + 1;
    }
} catch (\Exception $e) {
    die("Error during eNom API call. Try refreshing the page. Raw error: {$e->getMessage()}");
}

ksort($enomTldCounts);

$allDomains = array_unique(array_merge(array_keys($enomDomains), array_keys($whmcsDomains)));
sort($allDomains);

include(__DIR__ . '/inc/00-head.php');
?>
<style>
    .bad {
        font-weight: bold;
        color: red;
    }

    #tld-counts {
        margin-bottom: 20px;
    }
</style>
</head>
<body>
<h1>eNom Check</h1>
<div id="tld-counts">
    <h2>TLDs registered in eNom</h2>
    <table>
        <tbody>
        <?php foreach ($enomTldCounts as $tld => $count): ?>
            <tr>
                <td><?= $tld ?></td>
                <td><?= $count ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<h2>Overview</h2>
<table>
    <thead>
    <th>Domain</th>
    <th>In WHMCS?</th>
    <th>WHMCS: registrar</th>
    <th>eNom Expiration Date</th>
    <th>eNom Auto-Renew Enabled?</th>
    </thead>
    <tbody>
    <?php
    foreach ($allDomains as $domain):
        $whmcsDomain = isset($whmcsDomains[$domain]) ? $whmcsDomains[$domain] : null;
        $enomDomain = isset($enomDomains[$domain]) ? $enomDomains[$domain] : false;

        $registrar = $whmcsDomain ? $whmcsDomain['registrar'] : '';
        $registrarDisplay = '';
        if ($whmcsDomain) {
            $registrarDisplay = $registrar ?: 'None';
            if ($enomDomain) {
                $registrarDisplay = $registrar === 'enom' ? 'eNom' : "<span class='bad'>$registrarDisplay</span>";
            } else if ($registrar === 'enom') {
                $registrarDisplay = "<span class='bad'>eNom</span>";
            }
        }

        $enomExpDisplay = $enomDomain['expirationDate'];
        $enomAutoRenewDisplay = $enomDomain ? $enomDomain['autoRenew'] ? 'Yes' : 'No' : '';
        if ($enomDomain) {
            if ($enomDomain['autoRenew']) {
                // Bad, should be 'No', renewal only to be triggered by WHMCS after payment
                $enomAutoRenewDisplay = '<span class="bad">Yes</span>';
            }
        } elseif ($registrar === 'enom') {
            $enomExpDisplay = '<span class="bad">Missing</span>';
            $enomAutoRenewDisplay = '<span class="bad">Missing</span>';
        }
        ?>
        <tr>
            <td><?= $domain ?></td>
            <td><?= $whmcsDomain ? 'Yes' : '<span class="bad">Missing</span>' ?></td>
            <td><?= $registrarDisplay ?></td>
            <td><?= $enomExpDisplay ?></td>
            <td><?= $enomAutoRenewDisplay ?></td>
        </tr>
    <?php
    endforeach;
    ?>
    </tbody>
</table>
</body>
