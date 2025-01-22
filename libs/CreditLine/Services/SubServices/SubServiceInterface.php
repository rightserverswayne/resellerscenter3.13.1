<?php

namespace MGModule\ResellersCenter\libs\CreditLine\Services\SubServices;

use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\gateways\DeferredPayments\DeferredPayments;
use MGModule\ResellersCenter\Helpers\InvoicePaymentHelper;
use MGModule\ResellersCenter\libs\CreditLine\Interfaces\InvoiceModelInterface as Invoice;
use MGModule\ResellersCenter\models\CreditLine;
use MGModule\ResellersCenter\repository\CreditLines;
use MGModule\ResellersCenter\models\whmcs\Invoice as WhmcsInvoiceModel;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller as ResellerObj;

abstract class SubServiceInterface
{
    abstract function checkUserSettings($userId):bool;
    public abstract function checkIsAddCreditPossible(Invoice $invoice);
    public abstract function addCredit($invoice);
    public abstract function checkWhmcsCreditsAvailable(Invoice $invoice):bool;

    public function getEnableCreditLine($userId):?CreditLine
    {
        $creditLineRepo = new CreditLines();
        $creditLine = $creditLineRepo->getByClientId($userId)?: new CreditLine($userId);

        return ($creditLine->limit > 0 && $this->checkUserSettings($userId)) ? $creditLine : null;
    }

    public function isAddCreditAvailable(Invoice $invoice):bool
    {
        if (strtolower($invoice->paymentmethod) != DeferredPayments::SYS_NAME || $invoice->credit != 0) {
            return false;
        }

        $reseller = Server::isRunByCron() ? new ResellerObj($invoice->getReseller()) : ResellerHelper::getCurrent();

        if ($reseller->settings->admin->allowcreditline == 'on' &&
            $creditLine = $this->getEnableCreditLine($invoice->userid)) {
            return $creditLine->limit != 0 && ($creditLine->limit - $creditLine->usage) >= abs($invoice->total);
        } else {
            return false;
        }
    }

    public function makeInvoicePayment($invoice)
    {
        if ($invoice instanceof WhmcsInvoiceModel) {
            InvoicePaymentHelper::makeInvoicePayment($invoice);
        }
    }
}