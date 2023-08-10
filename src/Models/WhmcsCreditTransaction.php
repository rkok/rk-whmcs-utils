<?php

namespace RKWhmcsUtils\Models;

class WhmcsCreditTransaction
{
    protected int $id;
    protected int $clientId;
    protected int $adminId;
    protected \DateTime $date;
    protected string $description;
    protected float $amount;

    public static function fromDbRow(array $row) {
        return (new self())
            ->setId($row['id'])
            ->setClientId($row['clientid'])
            ->setAdminId($row['admin_id'])
            ->setDate(new \DateTime($row['date']))
            ->setDescription($row['description'])
            ->setAmount($row['amount']);
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
     * @return WhmcsCreditTransaction
     */
    public function setId(int $id): WhmcsCreditTransaction
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getClientId(): int
    {
        return $this->clientId;
    }

    /**
     * @param int $clientId
     * @return WhmcsCreditTransaction
     */
    public function setClientId(int $clientId): WhmcsCreditTransaction
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @return int
     */
    public function getAdminId(): int
    {
        return $this->adminId;
    }

    /**
     * @param int $adminId
     * @return WhmcsCreditTransaction
     */
    public function setAdminId(int $adminId): WhmcsCreditTransaction
    {
        $this->adminId = $adminId;
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
     * @return WhmcsCreditTransaction
     */
    public function setDate(\DateTime $date): WhmcsCreditTransaction
    {
        $this->date = $date;
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
     * @return WhmcsCreditTransaction
     */
    public function setDescription(string $description): WhmcsCreditTransaction
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
     * @return WhmcsCreditTransaction
     */
    public function setAmount(float $amount): WhmcsCreditTransaction
    {
        $this->amount = $amount;
        return $this;
    }
}
