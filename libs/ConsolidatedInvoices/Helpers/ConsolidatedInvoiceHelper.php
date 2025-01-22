<?php

namespace MGModule\ResellersCenter\libs\ConsolidatedInvoices\Helpers;

use DateTime;
use MGModule\ResellersCenter\gateways\DeferredPayments\DeferredPayments;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\Models\ConsolidatedInvoice;
use MGModule\ResellersCenter\libs\CreditLine\Interfaces\InvoiceModelInterface as Invoice;
use MGModule\ResellersCenter\repository\whmcs\Invoices;

class ConsolidatedInvoiceHelper
{
    const NUMBER_PREFIX = "Consolidated Invoice";
    const ZERO_PREFIX = "Zero Invoice";
    const CONSOLIDATED_PREFIX = "Consolidated";

    public static function getConsolidatedInvoiceForCurrentMonth(Invoice $invoice):ConsolidatedInvoice
    {
        $currentNumber = self::generateCurrentNumber();
        $invoicesRepo = $invoice->getRepository();
        $invoice = $invoicesRepo->where('userid', $invoice->userid)
            ->where('invoicenum', $currentNumber)
            ->where('status', Invoices::STATUS_DRAFT)
            ->first();
        $consolidateInvoice = new ConsolidatedInvoice();
        if ($invoice->exists) {
            $consolidateInvoice->setInvoiceModel($invoice);
        }
        return $consolidateInvoice;
    }

    public static function changeInvoiceToConsolidatedInvoice(Invoice $invoice):ConsolidatedInvoice
    {
        $currentNumber = self::generateCurrentNumber();
        $invoice->invoicenum = $currentNumber;
        $invoice->status = Invoices::STATUS_DRAFT;
        $invoice->paymentmethod = DeferredPayments::SYS_NAME;
        $invoice->save();
        $consolidateInvoice = new ConsolidatedInvoice();
        $consolidateInvoice->setInvoiceModel($invoice);
        return $consolidateInvoice;
    }

    public static function generateCurrentNumber():string
    {
        $date = new DateTime("NOW");
        $currentMonth = $date->format('m');
        $currentYear = $date->format('Y');

        return self::NUMBER_PREFIX . ' {' .$currentMonth.'} ' . '{'.$currentYear.'}';
    }

    public static function generateZeroNumber(Invoice $invoiceModel):string
    {
        $date = new DateTime($invoiceModel->date);
        $invoiceMonth = $date->format('m');
        $invoiceYear = $date->format('Y');

        return self::ZERO_PREFIX . ' {' .$invoiceMonth.'} ' . '{'.$invoiceYear.'}';
    }

    public static function generatePreviousMonthDate():DateTime
    {
        return new DateTime('last day of last month');
    }

    public static function getConsolidatedInvoicesForActivate(Invoice $invoiceModel):array
    {
        $consolidatedInvoices = [];
        $previousMonthDate = self::generatePreviousMonthDate();

        $invoicesRepo = $invoiceModel->getRepository();
        $invoices = $invoicesRepo->where('invoicenum', 'LIKE', self::CONSOLIDATED_PREFIX  . '%')
            ->where('status', Invoices::STATUS_DRAFT)
            ->where('date', '<=', $previousMonthDate->format('Y-m-d'))
            ->get();

        foreach ($invoices as $invoice) {
            $consolidateInvoice = new ConsolidatedInvoice();
            $consolidateInvoice->setInvoiceModel($invoice);
            $consolidatedInvoices[] = $consolidateInvoice;
        }

        return $consolidatedInvoices;
    }

}