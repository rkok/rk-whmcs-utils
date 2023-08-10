<?php

namespace RKWhmcsUtils\Models;

class WhmcsCommissionEntry
{
    protected int $id;
    protected \DateTime $date;
    protected int $affiliateId;
    protected int $affiliateAccountId;
    protected string $description;
    protected float $amount;
    protected ?int $invoiceId;

    /**
     * @throws \Exception
     */
    public static function fromDbRow(array $row): WhmcsCommissionEntry
    {
        return (new self())
            ->setId($row['id'])
            ->setDate(new \DateTime($row['date']))
            ->setAffiliateId($row['affiliateid'])
            ->setAffiliateAccountId($row['affaccid'])
            ->setDescription($row['description'])
            ->setAmount((float)$row['amount'])
            ->setInvoiceId($row['invoice_id'] === 0 ? null : $row['invoice_id']);
    }

    public function matchesTransaction(WhmcsTransaction $txn): bool {
        if (!$txn->getAffiliate()) {
            return false;
        }

        $invoice = $txn->getInvoice();
        if ($this->getInvoiceId() === $invoice->getId()) {
            return true;
        }
        // For older WHMCS versions, loose check, not safe!
        // TODO: figure out the real way to match
        // affiliates to referred clients and use it here
        return (
            $txn->calculateAffiliateCommission() === $this->getAmount()
            && $invoice->getDatePaid()->format('Ymd') === $this->getDate()->format('Ymd')
        );
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return WhmcsCommissionEntry
     */
    public function setId(int $id): WhmcsCommissionEntry
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return WhmcsCommissionEntry
     */
    public function setDate(\DateTime $date): WhmcsCommissionEntry
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return int
     */
    public function getAffiliateId(): int
    {
        return $this->affiliateId;
    }

    /**
     * @param int $affiliateId
     * @return WhmcsCommissionEntry
     */
    public function setAffiliateId(int $affiliateId): WhmcsCommissionEntry
    {
        $this->affiliateId = $affiliateId;
        return $this;
    }

    /**
     * @return int
     */
    public function getAffiliateAccountId(): int
    {
        return $this->affiliateAccountId;
    }

    /**
     * @param int $affiliateAccountId
     * @return WhmcsCommissionEntry
     */
    public function setAffiliateAccountId(int $affiliateAccountId): WhmcsCommissionEntry
    {
        $this->affiliateAccountId = $affiliateAccountId;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return WhmcsCommissionEntry
     */
    public function setDescription(string $description): WhmcsCommissionEntry
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return WhmcsCommissionEntry
     */
    public function setAmount(float $amount): WhmcsCommissionEntry
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getInvoiceId(): ?int
    {
        return $this->invoiceId;
    }

    /**
     * @param int|null $invoiceId
     * @return WhmcsCommissionEntry
     */
    public function setInvoiceId(?int $invoiceId): WhmcsCommissionEntry
    {
        $this->invoiceId = $invoiceId;
        return $this;
    }
}
