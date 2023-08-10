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
        $invoiceItems = $this->db->getInvoiceItemsByInvoiceId();
        $clients = $this->db->getClients();
        $affiliates = $this->db->getAffiliatesIndexedById();
        $clientAffiliateIds = $this->db->getClientAffiliateIds();

        $transactions = [];

        foreach ($invoices as $invoice) {
            if (isset($invoiceItems[$invoice->getId()])) {
                $invoice->setItems($invoiceItems[$invoice->getId()]);
            }

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
