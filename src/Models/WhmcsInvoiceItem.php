<?php

namespace RKWhmcsUtils\Models;

class WhmcsInvoiceItem
{
    protected int $id;
    protected int $invoiceId;
    protected int $userId;
    /**
     * @var string Hosting / PromoHosting / Addon / DomainRegister / Project / LateFee
     */
    protected string $type;
    protected string $description;
    protected int $amount;
    protected bool $taxed;
    protected ?\DateTime $dueDate;
    protected string $paymentMethod;
    protected string $notes;

    public static function fromDbRow(array $row): WhmcsInvoiceItem
    {
        return (new self())
            ->setId($row['id'])
            ->setInvoiceId($row['invoiceid'])
            ->setUserId($row['userid'])
            ->setType($row['type'])
            ->setDescription($row['description'])
            ->setAmount($row['amount'])
            ->setTaxed((bool)$row['taxed'])
            ->setDueDate($row['duedate'] ? new \DateTime($row['duedate']) : null)
            ->setPaymentMethod($row['paymentmethod'])
            ->setNotes($row['notes']);
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
     * @return WhmcsInvoiceItem
     */
    public function setId(int $id): WhmcsInvoiceItem
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getInvoiceId(): int
    {
        return $this->invoiceId;
    }

    /**
     * @param int $invoiceId
     * @return WhmcsInvoiceItem
     */
    public function setInvoiceId(int $invoiceId): WhmcsInvoiceItem
    {
        $this->invoiceId = $invoiceId;
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
     * @return WhmcsInvoiceItem
     */
    public function setUserId(int $userId): WhmcsInvoiceItem
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return WhmcsInvoiceItem
     */
    public function setType(string $type): WhmcsInvoiceItem
    {
        $this->type = $type;
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
     * @return WhmcsInvoiceItem
     */
    public function setDescription(string $description): WhmcsInvoiceItem
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return WhmcsInvoiceItem
     */
    public function setAmount(int $amount): WhmcsInvoiceItem
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTaxed(): bool
    {
        return $this->taxed;
    }

    /**
     * @param bool $taxed
     * @return WhmcsInvoiceItem
     */
    public function setTaxed(bool $taxed): WhmcsInvoiceItem
    {
        $this->taxed = $taxed;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDueDate(): ?\DateTime
    {
        return $this->dueDate;
    }

    /**
     * @param \DateTime|null $dueDate
     * @return WhmcsInvoiceItem
     */
    public function setDueDate(?\DateTime $dueDate): WhmcsInvoiceItem
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     * @return WhmcsInvoiceItem
     */
    public function setPaymentMethod(string $paymentMethod): WhmcsInvoiceItem
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotes(): string
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     * @return WhmcsInvoiceItem
     */
    public function setNotes(string $notes): WhmcsInvoiceItem
    {
        $this->notes = $notes;
        return $this;
    }
}
