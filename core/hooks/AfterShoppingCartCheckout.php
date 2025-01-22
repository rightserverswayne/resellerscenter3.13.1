<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\AfterCheckoutHelper;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice as ResellersCenterInvoice;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller as ResellerObj;
use MGModule\ResellersCenter\Core\Whmcs\Invoices\Invoice as WhmcsInvoice;

use MGModule\ResellersCenter\core\Redirect;
use MGModule\ResellersCenter\core\Session;
use MGModule\ResellersCenter\core\Server;
use MGModule\ResellersCenter\core\Request;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\Services\ConsolidatedInvoiceService;
use MGModule\ResellersCenter\libs\CreditLine\Services\CreditLineService;
use MGModule\ResellersCenter\models\Invoice as RcInvoice;
use MGModule\ResellersCenter\repository\Invoices;
use MGModule\ResellersCenter\repository\whmcs\Invoices as WhmcsInvoices;

/**
 * Description of AfterShoppingCartCheckout
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class AfterShoppingCartCheckout
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     *
     * @var array
     */
    public $functions;

    /**
     * Assign anonymous function
     */
    public function __construct()
    {
        $this->functions[10] = function($params)
        {
            return $this->addRelationsToReseller($params);
        };

        $this->functions[20] = function($params)
        {
            return $this->useConsolidatedInvoice($params);
        };

        $this->functions[30] = function($params)
        {
            return $this->removeZeroInvoices($params);
        };

        $this->functions[40] = function($params)
        {
            return $this->makeResellersCenterInvoicePayment($params);
        };

        $this->functions[PHP_INT_MAX - 2] = function($params)
        {
            return $this->moveOrderElements($params);
        };

        $this->functions[PHP_INT_MAX] = function($params)
        {
            return $this->clearAfterCheckout($params);
        };
    }

    /**
     * Add hostings, addons and domains relations to reseller
     *
     * @param type $params
     * @return type
     */
    public function addRelationsToReseller($params)
    {
        $reseller = Reseller::getCurrent();
        if (!$reseller->exists || basename(Server::get("SCRIPT_NAME")) == 'upgrade.php')
        {
            return $params;
        }

        //Add relation for services
        if($params["ServiceIDs"])
        {
            foreach ($params["ServiceIDs"] as $serviceid) {
                $reseller->hosting->assign($serviceid);
            }
        }

        //Add relation for addons
        if($params["AddonIDs"])
        {
            foreach ($params["AddonIDs"] as $addonid) {
                $reseller->addons->assign($addonid);
            }
        }

        //Add relation for domain
        if($params["DomainIDs"])
        {
            foreach ($params["DomainIDs"] as $domainid) {
                $reseller->domains->assign($domainid);
            }
        }

        return $params;
    }

    public function useConsolidatedInvoice($params)
    {
        $orderAlreadyActivated = Session::getAndClear(CreditLineService::ORDER_ACTIVATED_BY_CREDIT_LINE_FLAG);

        if (basename(Server::get("SCRIPT_NAME")) == 'upgrade.php') {
            return $params;
        }

        $invoices = new WhmcsInvoices();
        $invoice = $invoices->find($params["InvoiceID"]);

        if (!$invoice->exists) {
            return $params;
        }

        $consolidatedService = new ConsolidatedInvoiceService();

        $rcInvoice = RcInvoice::where('relinvoice_id', $invoice->id)->first();
        if ($rcInvoice->exists) {
            $consolidatedService->mergeRcInvoice($rcInvoice, !$orderAlreadyActivated);
        }

        $consolidatedService->setOrderId($params["OrderID"]);
        if ($consolidatedService->mergeWhmcsInvoice($invoice, !$orderAlreadyActivated)) {
            $_SESSION["orderdetails"]['InvoiceID'] = null;
        }

        return $params;
    }

    public function removeZeroInvoices($params)
    {
        if (basename(Server::get("SCRIPT_NAME")) == 'upgrade.php') {
            return $params;
        }

        $invoices = new WhmcsInvoices();
        $consolidatedService = new ConsolidatedInvoiceService();

        $invoice = $invoices->find($params["InvoiceID"]);
        $rcInvoice = RcInvoice::where('relinvoice_id', $invoice->id)->first();

        if (!$invoice->exists) {
            return $params;
        }

        $reseller =  new ResellerObj($invoice->getReseller());

        if (!$reseller->exists) {
            return $params;
        }

        $removeZeroInvoicesSetting = $reseller->settings->admin->removeZeroInvoices;

        if ($invoice->total == 0 &&
            $removeZeroInvoicesSetting &&
            !$consolidatedService->isConsolidatedInvoice($invoice)) {
            $invoice->items()->delete();
            $invoice->delete();
        }

        if (!$rcInvoice->exists) {
            return $params;
        }

        if ($rcInvoice->total == 0 &&
            $removeZeroInvoicesSetting &&
            !$consolidatedService->isConsolidatedInvoice($rcInvoice)) {
            $rcInvoice->items()->delete();
            $rcInvoice->delete();
        }

        return $params;
    }

    public function makeResellersCenterInvoicePayment($params)
    {
        if (!Request::get("submit")) {
            return $params;
        }

        if (Reseller::isMakingOrderForClient()) {
            return $params;
        }

        $reseller = Reseller::getCurrent();
        if (!$reseller->exists || !$reseller->settings->admin->resellerInvoice) {
            return $params;
        }

        if (basename(Server::get("SCRIPT_NAME")) == 'upgrade.php') {
            $_SESSION["upgradeorder"]["invoiceid"] = 0;
            Redirect::to(Server::getCurrentSystemURL(), "upgrade.php", ["step" => "4"]);
        }

        Session::set('rcPaymentDetails', Request::get());

        $_SESSION["orderdetails"]['InvoiceID'] = 0;
        Redirect::to(Server::getCurrentSystemURL(), "cart.php", ["a" => "complete"]);

        return $params;
    }

    /**
     *  Replace Service Owner - just in case (WHMCS8 changes)
     *
     * @param type $params
     * @return type
     */
    public function moveOrderElements($params)
    {
        //If reseller leave cart he no longer make order for client
        if (Reseller::isMakingOrderForClient())
        {
            /* Replace Service Owner - just in case (WHMCS8 changes) */
            if(Whmcs::isVersion('8.0')) {
                (new AfterCheckoutHelper($params))->changeServicesOwner($_SESSION['makeOrderFor']);
            }
        }
    }

    /**
     * Clear all variables that was set before to make an order
     *
     * @param type $params
     * @return type
     */
    public function clearAfterCheckout($params)
    {
        //If reseller leave cart he no longer make order for client
        if (Reseller::isMakingOrderForClient()) {
            //Check if invoice is paid - requires check by RC if invoice is paid on order with credits
            $invoice = new WhmcsInvoice($params["InvoiceID"]);

            if ($invoice->isReadyToProcess()) {
                processPaidInvoice($params["InvoiceID"]);
            }

            $resellerid = Session::get("resellerid");
            if (!empty($resellerid)) {
                Session::set("uid", $resellerid);
            }

            Session::clear("resellerid");
            Session::clear("makeOrderFor");
            Session::clear("orderdetails");
            //Reload page so WHMCS could load correct client information
            Redirect::toPageWithQuery("index.php", array("m" => "ResellersCenter", "mg-page" => "clients"));
        }

        Session::clear("domainPriceOverride");
        return $params;
    }
}