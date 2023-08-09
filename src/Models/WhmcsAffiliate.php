<?php

namespace RKWhmcsUtils\Models;

class WhmcsAffiliate
{
    protected int $id;
    protected int $clientId;
    protected int $userId;
    protected string $firstName;
    protected string $lastName;
    protected string $companyName;
    protected string $email;
    protected string $payType;
    protected float $payAmount;

    public static function fromDbRow(array $row): WhmcsAffiliate
    {
        return (new self())
            ->setId($row['id'])
            ->setClientId($row['clientid'])
            ->setUserId($row['userid'])
            ->setFirstName(htmlspecialchars_decode($row['firstname']))
            ->setLastName(htmlspecialchars_decode($row['lastname']))
            ->setCompanyName(htmlspecialchars_decode($row['companyname'] ?: ''))
            ->setEmail($row['email'])
            ->setPayType($row['paytype'])
            ->setPayAmount((float)$row['payamount']);
    }

    public function getDisplayName(): string
    {
        return $this->getCompanyName();
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
     * @return WhmcsAffiliate
     */
    public function setId(int $id): WhmcsAffiliate
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
     * @return WhmcsAffiliate
     */
    public function setClientId(int $clientId): WhmcsAffiliate
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return WhmcsAffiliate
     */
    public function setUserId(int $userId): WhmcsAffiliate
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return WhmcsAffiliate
     */
    public function setFirstName(string $firstName): WhmcsAffiliate
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return WhmcsAffiliate
     */
    public function setLastName(string $lastName): WhmcsAffiliate
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    /**
     * @param string $companyName
     * @return WhmcsAffiliate
     */
    public function setCompanyName(string $companyName): WhmcsAffiliate
    {
        $this->companyName = $companyName;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return WhmcsAffiliate
     */
    public function setEmail(string $email): WhmcsAffiliate
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getPayType(): string
    {
        return $this->payType;
    }

    /**
     * @param string $payType
     * @return WhmcsAffiliate
     */
    public function setPayType(string $payType): WhmcsAffiliate
    {
        $this->payType = $payType;
        return $this;
    }

    /**
     * @return float
     */
    public function getPayAmount(): float
    {
        return $this->payAmount;
    }

    /**
     * @param float $payAmount
     * @return WhmcsAffiliate
     */
    public function setPayAmount(float $payAmount): WhmcsAffiliate
    {
        $this->payAmount = $payAmount;
        return $this;
    }
}
