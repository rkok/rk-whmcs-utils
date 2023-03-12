<?php

namespace RKWhmcsUtils\Models;

class WhmcsClient
{
    protected int $id;
    protected ?string $companyName;
    protected string $firstName;
    protected string $lastName;

    public static function fromDbRow(array $row)
    {
        return (new self())
            ->setId($row['id'])
            ->setCompanyName($row['companyname'] ?: null)
            ->setFirstName($row['firstname'])
            ->setLastName($row['lastname']);
    }

    public function getDisplayName() {
        if ($this->getCompanyName()) {
            $name = trim($this->getCompanyName());
        } else {
            $name = trim($this->getFirstName() . ' ' . $this->getLastName());
        }
        return "$name ({$this->id})";
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
}
