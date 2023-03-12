<?php

namespace RKWhmcsUtils;

use PDO;
use RKWhmcsUtils\Models\WhmcsInvoice;

class WhmcsDb
{
    /**
     * @var PDO
     */
    private $pdo;

    public static function buildInstance()
    {
        $config = Config::getInstance();
        return new self($config->dbName, $config->dbUsername, $config->dbPassword, $config->dbHost);
    }

    public function __construct($dbName, $username, $password, $dbHost = '127.0.0.1:3306')
    {
        list($host, $port) = explode(':', $dbHost);
        $this->pdo = new PDO("mysql:dbname=$dbName;host=$host;port=" . $port ?: '3306', $username, $password, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    public function getAffiliates()
    {
        return $this->pdo->query("
            select a.id, a.clientid, uc.auth_user_id userid, c.firstname, c.lastname, c.companyname, c.email, a.paytype, a.payamount
            from tblaffiliates a
            join tblclients c on a.clientid = c.id 
            join tblusers_clients uc on c.id = uc.client_id
            where a.payamount != 0;
        ")->fetchAll(PDO::FETCH_UNIQUE);
    }

    public function getClientAffiliateIds()
    {
        return $this->pdo->query("
            select uc_child.client_id, a.id
            from tblaffiliates a
            join tblclients c on a.clientid = c.id
            join tblusers_clients uc on c.id = uc.client_id
            join tblusers u on uc.auth_user_id = u.id
            join tblusers_clients uc_child on uc_child.auth_user_id  = u.id
            where a.payamount != 0
            group by u.id, uc_child.client_id;
        ")->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function getClientsAndAssocAffiliates()
    {
        return $this->pdo->query("
            select c.id client_id, c.firstname, c.lastname, c.email,
                   c2.companyname aff_company, aff.payamount aff_payamount, aff.paytype aff_paytype
            from tblclients c
            join tblusers_clients uc on c.id = uc.client_id 
            left join tblusers u on uc.auth_user_id  = u.id and u.id in (
                select u.id 
                from tblaffiliates a
                join tblclients c on a.clientid = c.id
                join tblusers_clients uc on uc.client_id = c.id 
                join tblusers u on uc.auth_user_id = u.id
            )
            left join tblusers_clients uc2 on u.id = uc2.auth_user_id 
            left join tblclients c2 on uc2.client_id = c2.id
            left join tblaffiliates aff on uc2.client_id = aff.clientid
            group by c.id
            order by c.id;
        ")->fetchAll();
    }

    /**
     * @return array[] Indexed by domain name
     */
    public function getActiveDomains()
    {
        return $this->pdo->query("
            select domain, registrar
            from tbldomains
            where status = 'Active'
            order by domain asc
        ")->fetchAll(PDO::FETCH_UNIQUE);
    }

    public function getActiveDomainsListByClientId()
    {
        return $this->pdo->query("
            select d.userid, d.domain, d.recurringamount, d.registrationperiod, d.paymentmethod, d.additionalnotes notes
            from tbldomains d 
            left join tblusers u on d.userid = u.id
            where d.status = 'Active'
        ")->fetchAll(PDO::FETCH_GROUP);
    }

    public function getActiveServicesListByClientId()
    {
        return $this->pdo->query("
            select h.userid user_id, h.domain, h.paymentmethod, h.amount, h.billingcycle, h.notes
            from tblhosting h
            left join tblusers u on h.userid = u.id
            where h.domainstatus = 'Active'
        ")->fetchAll(PDO::FETCH_GROUP);
    }

    /**
     * @return array|false
     */
    public function getClients()
    {
        return $this->pdo->query("
            select *
            from tblclients
        ")->fetchAll(PDO::FETCH_UNIQUE);
    }

    /**
     * Ordered by ID desc (latest invoice first)
     * @return WhmcsInvoice[]
     */
    public function getInvoices(): array
    {
        // NOTE: deliberately aliasing 'userid' to 'clientid' -
        // there is a hideous misnomer hidden in WHMCS where tblinvoices.userid actually
        // refers to an entry in tblCLIENT, not tblUSERS!
        $results = $this->pdo->query("
            select *, userid as clientid
            from tblinvoices
            order by id desc
        ")->fetchAll();
        return array_map(function ($result) {
            return WhmcsInvoice::fromDbRow($result);
        }, $results ?? []);
    }

    /**
     * @return array|false
     */
    public function getInvoiceItemsByInvoiceId()
    {
        return $this->pdo->query("
            select invoiceid, i.*
            from tblinvoiceitems i
        ")->fetchAll(PDO::FETCH_GROUP);
    }

    /**
     * @return array|false
     */
    public function getUsers()
    {
        return $this->pdo->query("
            select *
            from tblusers
        ")->fetchAll(PDO::FETCH_UNIQUE);
    }

    /**
     * @return array|false
     */
    public function getClientsByUserId()
    {
        return $this->pdo->query("
            select u.id userid, uc.owner is_owner, uc.permissions, c.*
            from tblusers u
            left join tblusers_clients uc on u.id = uc.auth_user_id
            left join tblclients c on uc.client_id = c.id
            group by u.id, c.id
            having count(c.id) > 0
            order by u.id asc
        ")->fetchAll(PDO::FETCH_GROUP);
    }
}
