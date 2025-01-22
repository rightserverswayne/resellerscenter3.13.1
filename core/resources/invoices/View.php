<?php

namespace MGModule\ResellersCenter\Core\Resources\Invoices;

use \MGModule\ResellersCenter\Core\Resources\Pages\Invoices\Invoice as InvoicePage;
use \MGModule\ResellersCenter\models\Invoice as InvoiceModel;

/**
 * Description of View.php
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class View
{
    /**
     * @var Invoice
     */
    protected $invoice;

    /**
     * Initialize Message object
     *
     * @param Invoice $invoice
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public static function getClientAreaPageList(InvoiceModel $invoice):array
    {
        global $whmcs;

        $statusraw = strtolower($invoice->status);

        return [
            "id"          => $invoice->id,
            "invoicenum"  => $invoice->invoicenum ?: $invoice->id,
            "totalnum"    => $invoice->total,
            "total"       => formatCurrency($invoice->total + $invoice->credit),
            "balance"     => formatCurrency($invoice->total + $invoice->credit - $invoice->amountpaid),
            "status"      => Decorator::getStatusText($statusraw),
            "statusClass" => $statusraw,
            "rawstatus"   => $statusraw,
            "statustext"  => $whmcs->get_lang("invoices{$statusraw}"),
            "datedue"     => date("l, F jS, Y", strtotime($invoice->duedate)),
            "datecreated" => date("l, F jS, Y", strtotime($invoice->date)),
            "normalisedDateDue"     => date("Y-m-d", strtotime($invoice->duedate)),
            "normalisedDateCreated" => date("Y-m-d", strtotime($invoice->date)),
            "branded" => $invoice->whmcsInvoice->branded
        ];
    }

    /**
     * Display page on rcviewinvoice.php
     *
     * @param array $extra
     * @return string
     */
    public function getClientAreaPageView($extra = [])
    {
        $page = new InvoicePage();
        return $page->getView($this->invoice, $extra);
    }


}