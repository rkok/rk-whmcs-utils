<?php

namespace RKWhmcsUtils;

use PDO;
use RKWhmcsUtils\Models\WhmcsAffiliate;
use RKWhmcsUtils\Models\WhmcsAffiliateWithdrawal;
use RKWhmcsUtils\Models\WhmcsClient;
use RKWhmcsUtils\Models\WhmcsCommissionEntry;
use RKWhmcsUtils\Models\WhmcsCreditTransaction;
use RKWhmcsUtils\Models\WhmcsInvoice;
use RKWhmcsUtils\Models\WhmcsInvoiceItem;

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

    /**
     * @return WhmcsAffiliate[]
     */
    public function getAffiliatesIndexedById(): array
    {
        $results = $this->pdo->query("
            select a.id, a.clientid, uc.auth_user_id userid, c.firstname, c.lastname, c.companyname, c.email, a.paytype, a.payamount
            from tblaffiliates a
            join tblclients c on a.clientid = c.id 
            join tblusers_clients uc on c.id = uc.client_id
            where a.payamount != 0;
        ")->fetchAll();
        $return = [];
        foreach ($results as $row) {
            $return[$row['id']] = WhmcsAffiliate::fromDbRow($row);
        }
        return $return;
    }

    /**
     * @return WhmcsAffiliateWithdrawal[]
     */
    public function getAffiliateWithdrawals(): array
    {
        $results = $this->pdo->query('
            SELECT aw.id, aw.affiliateid, aw.`date`, aw.amount,
                IF(cr.id IS NULL, "cash", "credit") AS withdrawal_type,
                cr.id AS credit_id, cr.description AS credit_description,
                cr.amount AS credit_amount
            FROM tblaffiliateswithdrawals aw
            JOIN tblaffiliates a ON aw.affiliateid  = a.id
            LEFT JOIN tblcredit cr ON 
                cr.clientid = a.clientid 
                AND cr.date = aw.date 
                AND cr.amount = aw.amount
            GROUP BY aw.id;
        ')->fetchAll();
        $return = [];
        foreach ($results as $row) {
            $return[] = WhmcsAffiliateWithdrawal::fromDbRow($row);
        }
        return $return;
    }

    /**
     * @param $includeAffiliateCommissions
     * @return WhmcsCreditTransaction[]
     */
    public function getCreditTransactions($includeAffiliateCommissions = false)
    {
        // TODO: use a less hacky way of doing this (with joins like getAffiliateWithdrawals)
        $where = $includeAffiliateCommissions ? '' : 'WHERE description != "Affiliate Commissions Withdrawal"';
        $results = $this->pdo->query("
            SELECT id, clientid, admin_id, `date`, description, amount, relid
            FROM tblcredit
            $where;
        ")->fetchAll();
        $return = [];
        foreach ($results as $row) {
            $return[] = WhmcsCreditTransaction::fromDbRow($row);
        }
        return $return;
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
     * @return WhmcsClient[]
     */
    public function getClients(): array
    {
        $results = $this->pdo->query("
            select *
            from tblclients
        ")->fetchAll(PDO::FETCH_UNIQUE);
        $return = [];
        foreach ($results as $clientId => $row) {
            $return[$clientId] = WhmcsClient::fromDbRow([...$row, 'id' => $clientId]);
        }
        return $return;
    }

    /**
     * @return WhmcsCommissionEntry[]
     * @throws \Exception
     */
    public function getCommissionEntriesByAffiliateId(): array
    {
        $results = $this->pdo->query("
            select id, affiliateid, `date`, affaccid, invoice_id, description, amount, created_at, updated_at 
            from tblaffiliateshistory
        ")->fetchAll();
        return array_map(function ($row) {
            return WhmcsCommissionEntry::fromDbRow($row);
        }, $results);
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
            order by id asc
        ")->fetchAll();
        $return = [];
        foreach ($results as $row) {
            $return[$row['id']] = WhmcsInvoice::fromDbRow($row);
        }
        return $return;
    }

    /**
     * @return array|false
     */
    public function getInvoiceItemsByInvoiceId()
    {
        $results = $this->pdo->query("
            select invoiceid, i.*
            from tblinvoiceitems i
        ")->fetchAll(PDO::FETCH_GROUP);
        $return = [];
        foreach ($results as $invoiceId => $itemRows) {
            $return[$invoiceId] = array_map(function ($itemRow) {
                return WhmcsInvoiceItem::fromDbRow($itemRow);
            }, $itemRows);
        }
        return $return;
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
