<?php

namespace RKWhmcsUtils;

class Config
{
    /**
     * @var Config
     */
    private static $instance;
    public $dbHost;
    public $dbName;
    public $dbUsername;
    public $dbPassword;
    public $enomUser;
    public $enomKey;
    public $whmcsAdminRoot;
    public $whmcsRoot;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $configPath = __DIR__ . '/../config.php';
        if (!file_exists($configPath)) {
            throw new \Exception('config.php not found');
        }
        $config = require($configPath);
        $this->dbHost = $config['dbHost'];
        $this->dbName = $config['dbName'];
        $this->dbUsername = $config['dbUsername'];
        $this->dbPassword = $config['dbPassword'];
        $this->enomUser = $config['enomUser'];
        $this->enomKey = $config['enomKey'];
        $this->whmcsAdminRoot = $config['whmcsAdminRoot'] ?: '/admin/';
        $this->whmcsRoot = $config['whmcsRoot'] ?: '/';
    }
}
