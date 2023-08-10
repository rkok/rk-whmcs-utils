<?php

namespace RKWhmcsUtils\Models;

class GnucashTransactionLine
{
    const GNUCASH_CSV_COLS = [
        'Date',
        'Transaction ID',
        'Number',
        'Description',
        'Notes',
        'Commodity/Currency',
        'Void Reason',
        'Action',
        'Memo',
        'Full Account Name',
        'Account Name',
        'Amount With Sym',
        'Amount Num.',
        'Value With Sym',
        'Value Num.',
        'Reconcile',
        'Reconcile Date',
        'Rate/Price'
    ];

    protected string $date;
    protected string $id;
    protected string $description;
    protected string $notes = '';
    protected string $memo = '';
    protected string $fullAccountName;
    protected float $amount;
    protected string $reconciledStatus = 'n';

    public function toArray() {
        return [
            $this->date,
            $this->id,
            '', // Number
            $this->description,
            $this->notes,
            '', // Commodity/Currency (TODO)
            '', // Void Reason,
            '', // Action
            $this->memo, // Memo
            $this->fullAccountName, // Full account name
            '', // Account Name (unused)
            '', // Amount With Sym
            $this->formatAmount($this->amount), // Amount Num.
            '', // Value With Sym
            $this->formatAmount($this->amount), // Value Num.
            $this->reconciledStatus, // Reconciled (n = new, c = cleared)
            '', // Reconcile Date
            1, // Rate/Price
        ];
    }

    protected function formatAmount($amount) {
        return number_format($amount, 2, ',', '');
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @return GnucashTransactionLine
     */
    public function setDate(string $date): GnucashTransactionLine
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return GnucashTransactionLine
     */
    public function setId(string $id): GnucashTransactionLine
    {
        $this->id = $id;
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
     * @return GnucashTransactionLine
     */
    public function setDescription(string $description): GnucashTransactionLine
    {
        $this->description = $description;
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
     * @return GnucashTransactionLine
     */
    public function setNotes(string $notes): GnucashTransactionLine
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return string
     */
    public function getMemo(): string
    {
        return $this->memo;
    }

    /**
     * @param string $memo
     * @return GnucashTransactionLine
     */
    public function setMemo(string $memo): GnucashTransactionLine
    {
        $this->memo = $memo;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullAccountName(): string
    {
        return $this->fullAccountName;
    }

    /**
     * @param string $fullAccountName
     * @return GnucashTransactionLine
     */
    public function setFullAccountName(string $fullAccountName): GnucashTransactionLine
    {
        $this->fullAccountName = $fullAccountName;
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
     * @return GnucashTransactionLine
     */
    public function setAmount(float $amount): GnucashTransactionLine
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getReconciledStatus(): string
    {
        return $this->reconciledStatus;
    }

    /**
     * @param string $reconciledStatus
     * @return GnucashTransactionLine
     */
    public function setReconciledStatus(string $reconciledStatus): GnucashTransactionLine
    {
        $this->reconciledStatus = $reconciledStatus;
        return $this;
    }
}
