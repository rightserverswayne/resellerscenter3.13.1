<?php

namespace MGModule\ResellersCenter\libs\ResellerClientsCases;

use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\repository\whmcs\Invoices;
use MGModule\ResellersCenter\repository\whmcs\Transactions;

abstract class AbstractCase
{
    protected $availableIds = [];
    protected $invoicesRepo;

    abstract function getServicesIds();
    abstract function getInvoicesFromWhmcsInvoices($invoices);
    abstract function getInvoicesCounters();
    abstract function getUnpaidInvoicesOverdueCount();
    abstract protected function getAvailableInvoicesIds():array;

    public function __construct()
    {
        $this->invoicesRepo = new Invoices();
    }

    public function getInvoiceItemsFromWhmcsInvoiceItems($invoiceItems)
    {
        $invoiceItemsList = $invoiceItems ?: [];

        $availableIds = $this->getAvailableInvoicesIds();

        return array_filter($invoiceItemsList, function ($invoice, $invoiceId) use ($availableIds){
            return in_array($invoiceId, $availableIds);
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function getInvoicesPartialPayments()
    {
        $transactionsRepo = new Transactions();
        return $transactionsRepo->getTransactionsBalanceByInvoiceIds($this->getAvailableInvoicesIds());
    }

    public function getInvoicesCredit()
    {
        return $this->invoicesRepo->getSummaryCreditByInvoiceIds($this->getAvailableInvoicesIds());
    }

    public function getUnpaidInvoicesBalance()
    {
        return $this->invoicesRepo->getBalanceByInvoicesIds($this->getAvailableInvoicesIds());
    }

    public function getUnpaidInvoicesOverdueBalance()
    {
        return $this->invoicesRepo->getOverdueBalanceByInvoicesIds($this->getAvailableInvoicesIds());
    }

    public function getInvoicesSubTotal($invoices)
    {
        return $this->invoicesRepo->getInvoicesSubTotalByInvoicesIds($invoices);
    }

    public function getInvoicesTotal($invoices)
    {
        return $this->invoicesRepo->getInvoicesTotalByInvoicesIds($invoices);
    }

    public function getInvoicesTotalTax($invoices)
    {
        return $this->invoicesRepo->getInvoicesTotalTaxByInvoicesIds($invoices);
    }

    public function getInvoicesTotalTax2($invoices)
    {
        return $this->invoicesRepo->getInvoicesTotalTax2ByInvoicesIds($invoices);
    }

    public function getParentInvoiceId()
    {
        return $this->invoicesRepo->getParentInvoiceIdFromAvailableIds($this->getAvailableInvoicesIds());
    }
}
