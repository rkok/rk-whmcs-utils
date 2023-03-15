<?php

namespace RKWhmcsUtils;

use RKWhmcsUtils\Models\WhmcsTransaction;

class WhmcsRepository
{
    /**
     * @var WhmcsDb
     */
    private $db;

    public function __construct(WhmcsDb $db)
    {
        $this->db = $db;
    }

    /**
     * @return WhmcsTransaction[]
     */
    public function getTransactionList(): array
    {
        $invoices = $this->db->getInvoices();
        $clients = $this->db->getClients();
        $affiliates = $this->db->getAffiliates();
        $clientAffiliateIds = $this->db->getClientAffiliateIds();

        $transactions = [];

        foreach ($invoices as $invoice) {
            $transaction = new WhmcsTransaction($invoice);

            if (isset($clients[$invoice->getClientId()])) {
                $transaction->setClient($clients[$invoice->getClientId()]);
            }

            if (isset($clientAffiliateIds[$invoice->getClientId()])) {
                $transaction->setAffiliate($affiliates[$clientAffiliateIds[$invoice->getClientId()]]);
            }

            $transactions[] = $transaction;
        }

        return $transactions;
    }
}