<?php

// Copy to 'config.php' and edit

if (file_exists(__DIR__ . '/../../configuration.php')) {
    require_once(__DIR__ . '/../../configuration.php');
}

return [
    'dbHost' => $db_host,
    'dbName' => $db_name,
    'dbUsername' => $db_username,
    'dbPassword' => $db_password,
    'enomUser' => 'myenomuser', // TODO: extract from WHMCS DB?
    'enomKey' => 'BEEF',
    'whmcsAdminRoot' => 'https://mywebsite.com/admin/', // Optional
    'whmcsRoot' => 'https://mywebsite.com/', // Optional
];
