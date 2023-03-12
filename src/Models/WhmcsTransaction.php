<?php

namespace RKWhmcsUtils\Models;

class WhmcsTransaction
{
    protected WhmcsInvoice $invoice;
    protected $clientId;
    protected $clientName;
    protected $invoiceStatus;
    protected $invoiceDatePaid;
    protected $invoicePaymentMethod;
    protected $subTotal;
    protected $tax;
    protected $taxRate;
    protected $credit;
    protected $total;
    protected $userId;
    protected $affiliate;
    protected $commission;
}
