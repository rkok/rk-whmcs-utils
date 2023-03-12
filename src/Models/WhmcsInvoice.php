<?php

namespace RKWhmcsUtils\Models;

class WhmcsInvoice
{
    protected int $id;
    protected int $userId;
    protected float $subTotal;
    protected float $tax;
    protected float $taxRate;
    protected float $credit;
    protected float $total;
    protected int $clientId;
    protected string $status;
    protected \DateTime $date;
    protected ?\DateTime $datePaid;
    protected string $paymentMethod;

    public static function fromDbRow(array $row): WhmcsInvoice
    {
        return (new self())
            ->setId($row['id'])
            ->setClientId($row['clientid'])
            ->setUserId($row['userid'])
            ->setDate(new \DateTime($row['date']))
            ->setDatePaid($row['datepaid'] ? new \DateTime($row['datepaid']) : null)
            ->setSubTotal((float)$row['subtotal'])
            ->setCredit((float)$row['credit'])
            ->setTax((float)$row['tax'])
            ->setTaxRate((float)$row['taxrate'])
            ->setTotal((float)$row['total'])
            ->setStatus($row['status'])
            ->setPaymentMethod($row['paymentmethod']);
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
     * @return WhmcsInvoice
     */
    public function setId(int $id): WhmcsInvoice
    {
        $this->id = $id;
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
     * @return WhmcsInvoice
     */
    public function setUserId(int $userId): WhmcsInvoice
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return float
     */
    public function getSubTotal(): float
    {
        return $this->subTotal;
    }

    /**
     * @param float $subTotal
     * @return WhmcsInvoice
     */
    public function setSubTotal(float $subTotal): WhmcsInvoice
    {
        $this->subTotal = $subTotal;
        return $this;
    }

    /**
     * @return float
     */
    public function getTax(): float
    {
        return $this->tax;
    }

    /**
     * @param float $tax
     * @return WhmcsInvoice
     */
    public function setTax(float $tax): WhmcsInvoice
    {
        $this->tax = $tax;
        return $this;
    }

    /**
     * @return float
     */
    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    /**
     * @param float $taxRate
     * @return WhmcsInvoice
     */
    public function setTaxRate(float $taxRate): WhmcsInvoice
    {
        $this->taxRate = $taxRate;
        return $this;
    }

    /**
     * @return float
     */
    public function getCredit(): float
    {
        return $this->credit;
    }

    /**
     * @param float $credit
     * @return WhmcsInvoice
     */
    public function setCredit(float $credit): WhmcsInvoice
    {
        $this->credit = $credit;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * @param float $total
     * @return WhmcsInvoice
     */
    public function setTotal(float $total): WhmcsInvoice
    {
        $this->total = $total;
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
     * @return WhmcsInvoice
     */
    public function setClientId(int $clientId): WhmcsInvoice
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return WhmcsInvoice
     */
    public function setStatus(string $status): WhmcsInvoice
    {
        $this->status = $status;
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
     * @return WhmcsInvoice
     */
    public function setDate(\DateTime $date): WhmcsInvoice
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDatePaid(): \DateTime
    {
        return $this->datePaid;
    }

    /**
     * @param \DateTime $datePaid
     * @return WhmcsInvoice
     */
    public function setDatePaid(\DateTime $datePaid): WhmcsInvoice
    {
        $this->datePaid = $datePaid;
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
     * @return WhmcsInvoice
     */
    public function setPaymentMethod(string $paymentMethod): WhmcsInvoice
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }
}
