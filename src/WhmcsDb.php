<?php

namespace RKWhmcsUtils;

use PDO;

class WhmcsDb
{
    /**
     * @var PDO
     */
    private $pdo;

    public static function buildInstance()
    {
        $config = Config::getInstance();
        return new self($config->dbName, $config->dbUsername, $config->dbPassword);
    }

    public function __construct($dbName, $username, $password)
    {
        $this->pdo = new PDO("mysql:dbname=$dbName", $username, $password, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    public function getActiveUsersList()
    {
        return $this->pdo->query("
            select u.id user_id, u.email, c.id client_id, c.firstname, c.lastname, 
                   c2.companyname aff_company, a.payamount aff_payamount, a.paytype aff_paytype
            from tblclients c
            join (select * from tblusers_clients uc group by uc.auth_user_id) uc on uc.client_id  = c.id
            join tblusers u on uc.auth_user_id = u.id
            left join tblaffiliatesaccounts aa on aa.relid = u.id
            left join tblaffiliates a on aa.affiliateid = a.id
            left join tblclients c2 on a.clientid = c2.id  
            where c.status = 'Active'
            order by user_id asc
        ")->fetchAll();
    }

    /**
     * @return string[]
     */
    public function getActiveDomainNames()
    {
        return $this->pdo->query("
            select domain
            from tbldomains
            where status = 'Active'
            order by domain asc
        ")->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getActiveDomainsListByUserId()
    {
        return $this->pdo->query("
            select d.userid, d.domain, d.recurringamount, d.registrationperiod, d.paymentmethod, d.additionalnotes notes
            from tbldomains d 
            left join tblusers u on d.userid = u.id
            where d.status = 'Active'
        ")->fetchAll(PDO::FETCH_GROUP);
    }

    public function getActiveServicesListByUserId()
    {
        return $this->pdo->query("
            select h.userid user_id, h.domain, h.paymentmethod, h.amount, h.billingcycle, h.notes
            from tblhosting h
            left join tblusers u on h.userid = u.id
            where h.domainstatus = 'Active'
        ")->fetchAll(PDO::FETCH_GROUP);
    }
}
