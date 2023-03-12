<?php

namespace RKWhmcsUtils\Models;

class WhmcsTransaction
{
    protected WhmcsInvoice $invoice;
    protected ?WhmcsClient $client = null;
    protected ?WhmcsAffiliate $affiliate = null;

    public function __construct(WhmcsInvoice $invoice) {
        $this->invoice = $invoice;
    }

    /**
     * @return float|null
     * @throws \Exception
     */
    public function calculateAffiliateCommission(): ?float {
        if ($aff = $this->affiliate) {
            if ($aff->getPayType() === 'percentage') {
                return $this->invoice->getSubTotal() * ($aff->getPayAmount() / 100);
            }
            throw new \Exception("Unsupported affiliate pay type: {$aff->getPayType()}");
        }
        return null;
    }

    /**
     * @return WhmcsInvoice
     */
    public function getInvoice(): WhmcsInvoice
    {
        return $this->invoice;
    }

    /**
     * @param WhmcsInvoice $invoice
     * @return WhmcsTransaction
     */
    public function setInvoice(WhmcsInvoice $invoice): WhmcsTransaction
    {
        $this->invoice = $invoice;
        return $this;
    }

    /**
     * @return WhmcsClient|null
     */
    public function getClient(): ?WhmcsClient
    {
        return $this->client;
    }

    /**
     * @param WhmcsClient|null $client
     * @return WhmcsTransaction
     */
    public function setClient(?WhmcsClient $client): WhmcsTransaction
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return WhmcsAffiliate|null
     */
    public function getAffiliate(): ?WhmcsAffiliate
    {
        return $this->affiliate;
    }

    /**
     * @param WhmcsAffiliate|null $affiliate
     * @return WhmcsTransaction
     */
    public function setAffiliate(?WhmcsAffiliate $affiliate): WhmcsTransaction
    {
        $this->affiliate = $affiliate;
        return $this;
    }
}
