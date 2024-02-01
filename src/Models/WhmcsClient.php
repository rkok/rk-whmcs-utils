<?php

namespace RKWhmcsUtils\Models;

class WhmcsClient
{
    protected int $id;
    protected ?string $companyName;
    protected string $firstName;
    protected string $lastName;
    protected string $email;
    protected ?WhmcsCurrency $currency;

    public static function fromDbRow(array $row)
    {
        return (new self())
            ->setId($row['id'])
            ->setCompanyName(htmlspecialchars_decode($row['companyname'] ?: ''))
            ->setFirstName(htmlspecialchars_decode($row['firstname']))
            ->setLastName(htmlspecialchars_decode($row['lastname']))
            ->setEmail($row['email']);
    }

    public function generateClientViewUrlPath(): string
    {
        return "clientssummary.php?userid=" . $this->getId();
    }

    public function getDisplayName()
    {
        if ($this->getCompanyName()) {
            $name = trim($this->getCompanyName());
        } else {
            $name = $this->getFullNameFormatted();
        }
        return "$name (ID $this->id)";
    }

    public function getFullNameFormatted()
    {
        return trim(ucfirst($this->getFirstName()) . ' ' . ucfirst($this->getLastName()));
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
     * @return WhmcsClient
     */
    public function setId(int $id): WhmcsClient
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    /**
     * @param string|null $companyName
     * @return WhmcsClient
     */
    public function setCompanyName(?string $companyName): WhmcsClient
    {
        $this->companyName = $companyName;
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
     * @return WhmcsClient
     */
    public function setFirstName(string $firstName): WhmcsClient
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
     * @return WhmcsClient
     */
    public function setLastName(string $lastName): WhmcsClient
    {
        $this->lastName = $lastName;
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
     * @return WhmcsClient
     */
    public function setEmail(string $email): WhmcsClient
    {
        $this->email = $email;
        return $this;
    }

    public function getCurrency(): ?WhmcsCurrency
    {
        return $this->currency;
    }

    public function setCurrency(?WhmcsCurrency $currency): WhmcsClient
    {
        $this->currency = $currency;
        return $this;
    }
}
