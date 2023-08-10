<?php

namespace RKWhmcsUtils\Models;

class WhmcsAffiliateWithdrawal
{
    protected int $id;
    protected int $affiliateId;
    protected \DateTime $date;
    protected float $amount;
    protected string $withdrawalType;
    protected ?int $creditId;
    protected ?string $creditDescription;
    protected ?float $creditAmount;

    public static function fromDbRow(array $row) {
        return (new self())
            ->setId($row['id'])
            ->setAffiliateId($row['affiliateid'])
            ->setDate(new \DateTime($row['date']))
            ->setAmount($row['amount'])
            ->setWithdrawalType($row['withdrawal_type'])
            ->setCreditId($row['credit_id'])
            ->setCreditDescription($row['credit_description'])
            ->setCreditAmount($row['credit_amount']);
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
     * @return WhmcsAffiliateWithdrawal
     */
    public function setId(int $id): WhmcsAffiliateWithdrawal
    {
        $this->id = $id;
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
     * @return WhmcsAffiliateWithdrawal
     */
    public function setAffiliateId(int $affiliateId): WhmcsAffiliateWithdrawal
    {
        $this->affiliateId = $affiliateId;
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
     * @return WhmcsAffiliateWithdrawal
     */
    public function setDate(\DateTime $date): WhmcsAffiliateWithdrawal
    {
        $this->date = $date;
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
     * @return WhmcsAffiliateWithdrawal
     */
    public function setAmount(float $amount): WhmcsAffiliateWithdrawal
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getWithdrawalType(): string
    {
        return $this->withdrawalType;
    }

    /**
     * @param string $withdrawalType
     * @return WhmcsAffiliateWithdrawal
     */
    public function setWithdrawalType(string $withdrawalType): WhmcsAffiliateWithdrawal
    {
        $this->withdrawalType = $withdrawalType;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCreditId(): ?int
    {
        return $this->creditId;
    }

    /**
     * @param int|null $creditId
     * @return WhmcsAffiliateWithdrawal
     */
    public function setCreditId(?int $creditId): WhmcsAffiliateWithdrawal
    {
        $this->creditId = $creditId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCreditDescription(): ?string
    {
        return $this->creditDescription;
    }

    /**
     * @param string|null $creditDescription
     * @return WhmcsAffiliateWithdrawal
     */
    public function setCreditDescription(?string $creditDescription): WhmcsAffiliateWithdrawal
    {
        $this->creditDescription = $creditDescription;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getCreditAmount(): ?float
    {
        return $this->creditAmount;
    }

    /**
     * @param float|null $creditAmount
     * @return WhmcsAffiliateWithdrawal
     */
    public function setCreditAmount(?float $creditAmount): WhmcsAffiliateWithdrawal
    {
        $this->creditAmount = $creditAmount;
        return $this;
    }
}
