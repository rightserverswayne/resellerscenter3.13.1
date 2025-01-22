<?php

namespace MGModule\ResellersCenter\libs\ConsolidatedInvoices\Models;

use DateInterval;
use DateTime;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller as ResellerObj;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings\ConsolidatedInvoicesGenerationDay;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings\DisableZeroInvoices;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\Helpers\ConsolidatedInvoiceHelper;
use MGModule\ResellersCenter\libs\CreditLine\Interfaces\InvoiceModelInterface as Invoice;
use MGModule\ResellersCenter\models\Invoice as RcInvoice;
use MGModule\ResellersCenter\models\whmcs\Order;
use MGModule\ResellersCenter\models\whmcs\Invoice as WhmcsInvoice;;
use MGModule\ResellersCenter\repository\whmcs\Invoices;

class ConsolidatedInvoice
{
    protected $model;

    public function setInvoiceModel(Invoice $invoice)
    {
        $this->model = $invoice;
    }

    public function getId()
    {
        return $this->model->id;
    }

    public function getNumber()
    {
        return $this->model->invoicenum;
    }

    public function exists()
    {
        return $this->model != null;
    }

    public function collectItemsFromInvoice(Invoice $invoice)
    {
        $reflectionClass = new \ReflectionClass($invoice);
        $invoiceIdColumn = $reflectionClass->getName() == RcInvoice::class ? 'invoice_id' : 'invoiceid';
        foreach ($invoice->items as $item) {
            $item->$invoiceIdColumn = $this->model->id;
            $item->save();
        }
    }

    public function assignToOrder($orderId)
    {
        $invoice = $this->model;

        if (!is_a($invoice, WhmcsInvoice::class)) {
            return;
        }

        $order = Order::where('id', $orderId)->first();
        if ($order->exists) {
            $order->invoiceid = $invoice->id;
            $order->save();
        }
    }

    public function recalculateInvoiceItems()
    {
        $this->model->recalculate();
    }

    public function getReseller()
    {
        return new ResellerObj($this->model->getReseller());
    }

    public function activate()
    {
        $nowDate = new DateTime("NOW");
        $daysCountInMonth = $nowDate->format('t');
        $dayOfMonth = $nowDate->format('j');
        $generatingDay = SettingsManager::getSetting($this->model->userid, ConsolidatedInvoicesGenerationDay::NAME);

        $reseller = $this->getReseller();
        $disableZeroInvoices = SettingsManager::getSettingFromReseller($reseller, DisableZeroInvoices::NAME);

        if ($dayOfMonth >= $generatingDay || $dayOfMonth == $daysCountInMonth) {
            global $whmcs;
            $dueDateDays = $whmcs->get_config("CreateInvoiceDaysBefore");
            $interval = new DateInterval("P{$dueDateDays}D");
            $dueDate = clone $nowDate;
            $dueDate->add($interval);
            $this->model->invoicenum = ($this->model->total == 0 && $disableZeroInvoices) ?
                ConsolidatedInvoiceHelper::generateZeroNumber($this->model) : $this->model->setCustomInvoiceNumber();
            $this->model->date = $nowDate->format('Y-m-d');
            $this->model->duedate = $dueDate->format('Y-m-d');
            $this->model->status = ($this->model->total == 0 && $disableZeroInvoices) ? Invoices::STATUS_DRAFT : Invoices::STATUS_UNPAID;
            $this->model->save();
            return "Activated";
        } else {
            return "Failed. Different generation days. <br> Consolidated generation day: " . $generatingDay. " <br> Today day: ". $dayOfMonth;
        }
    }
}