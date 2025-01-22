<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\CartHelper;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller as ResellerObj;
use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\Services\ConsolidatedInvoiceService;
use MGModule\ResellersCenter\models\Invoice as RcInvoice;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\libs\CreditLine\Services\CreditLineService;
use MGModule\ResellersCenter\repository\whmcs\Invoices as WhmcsInvoices;

class InvoiceCreated
{
    public $functions;
    public static $params;

    public function __construct()
    {

        $this->functions[10] = function($params)
        {
            self::$params = $this->fixMassPaymentInvoiceCancellation($params);
        };

        $this->functions[15] = function($params)
        {
            self::$params = $this->fixResellerInvoicesTax($params);
        };

        $this->functions[20] = function($params)
        {
            self::$params = $this->useCreditLine(self::$params);
        };

        $this->functions[30] = function($params)
        {
            self::$params = $this->useConsolidatedInvoice(self::$params);
        };

        $this->functions[PHP_INT_MAX] = function($params)
        {
            self::$params = $this->removeZeroInvoices(self::$params);
        };
    }

    private function fixMassPaymentInvoiceCancellation($params)
    {
        $userId = Session::get('uid');
        $isReseller = ResellerHelper::isReseller($userId);
        $isResellerClient = (new ResellersClients())->getByRelid($userId)->exists;

        if ($isReseller || !$isResellerClient || $params['source'] != 'autogen' || $params['status'] != 'Unpaid') {
            return $params;
        }

        $invoiceRepo = new WhmcsInvoices();

        if ($invoiceRepo->isParentInvoice($params['invoiceid'])) {
            if ($invoiceRepo->isResellerRelated($params['invoiceid'])) {
                $invoiceRepo->activeLastCancelledNorelatedInvoice($userId);
            } else {
                $invoiceRepo->activeLastCancelledRelatedInvoice($userId);
            }
        }

        return $params;
    }

    public function fixResellerInvoicesTax($params)
    {
        $invoices = new WhmcsInvoices();
        $invoice = $invoices->find($params["invoiceid"]);

        if ($invoice->resellerInvoice->exists) {
            $taxes = CartHelper::getTaxes($invoice->resellerInvoice->reseller->client);

            $invoice->taxrate = $taxes['tax1']["rate"];
            $invoice->taxrate2 = $taxes['tax2']["rate"];

            $invoice->save();
            $invoice->updateInvoiceTotal();
        }

        return $params;
    }

    public function useCreditLine($params)
    {
        $invoices = new WhmcsInvoices();
        $invoice = $invoices->find($params["invoiceid"]);

        if (!$invoice->exists || $invoice->status == WhmcsInvoices::STATUS_PAID) {
            return $params;
        }

        $creditLineService = new CreditLineService();
        if ($creditLineService->addCreditAndActiveOrder($invoice)) {
            $_SESSION[CreditLineService::ORDER_ACTIVATED_BY_CREDIT_LINE_FLAG] = true;
        }

        return $params;
    }

    public function useConsolidatedInvoice($params)
    {
        $isUpgrade = basename(Server::get("SCRIPT_NAME")) == 'upgrade.php';
        $preventRedirectAfterUpgrade = null;

        if (!($isUpgrade || Server::isRunByCron())) {
            return $params;
        }

        $orderAlreadyActivated = Session::getAndClear(CreditLineService::ORDER_ACTIVATED_BY_CREDIT_LINE_FLAG);

        $invoices = new WhmcsInvoices();
        $invoice = $invoices->find($params["invoiceid"]);

        if (!$invoice->exists) {
            return $params;
        }

        $consolidatedService = new ConsolidatedInvoiceService();
        $consolidatedService->setOrderId($invoice->getOrderIdFromItems());

        $rcInvoice = RcInvoice::where('relinvoice_id', $invoice->id)->first();
        if ($rcInvoice->exists) {
            $preventRedirectAfterUpgrade = $consolidatedService->mergeRcInvoice($rcInvoice, !$orderAlreadyActivated) && $isUpgrade;
        }

        $whmcsUpgradeInvoiceMerged = $consolidatedService->mergeWhmcsInvoice($invoice, !$orderAlreadyActivated) && $isUpgrade;

        $_SESSION["RC_preventRedirectAfterUpgrade"] = $preventRedirectAfterUpgrade !== null ? $preventRedirectAfterUpgrade : $whmcsUpgradeInvoiceMerged;

        return $params;
    }

    public function removeZeroInvoices($params)
    {
        $isUpgrade = basename(Server::get("SCRIPT_NAME")) == 'upgrade.php';

        if (!($isUpgrade || Server::isRunByCron())) {
            return $params;
        }
        
        $invoices = new WhmcsInvoices();
        $consolidatedService = new ConsolidatedInvoiceService();

        $invoice = $invoices->find($params["invoiceid"]);
        $rcInvoice = RcInvoice::where('relinvoice_id', $invoice->id)->first();

        if (!$invoice->exists) {
            return $params;
        }

        $reseller =  new ResellerObj($invoice->getReseller());

        if (!$reseller->exists) {
            return $params;
        }

        if ($invoice->total == 0 &&
            $reseller->settings->admin->removeZeroInvoices &&
            !$consolidatedService->isConsolidatedInvoice($invoice)) {
            $invoice->items()->delete();
            $invoice->delete();
        }

        if (!$rcInvoice->exists) {
            return $params;
        }

        if ($rcInvoice->total == 0 &&
            $reseller->settings->admin->removeZeroInvoices &&
            !$consolidatedService->isConsolidatedInvoice($rcInvoice)) {
            $rcInvoice->items()->delete();
            $rcInvoice->delete();
        }

        return $params;
    }
}