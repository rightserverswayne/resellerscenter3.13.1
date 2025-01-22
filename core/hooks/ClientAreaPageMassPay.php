<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\libs\ResellerClientsCases\CasesFactory;
use MGModule\ResellersCenter\repository\ResellersClients;

class ClientAreaPageMassPay
{
    public $functions;
    public static $params;

    public function __construct()
    {
        $this->functions[10] = function ($params) {
            self::$params = $this->setInvoices($params);
        };

        $this->functions[200] = function($params)
        {
            //Fix for WHMCS 7.2.1
            global $smartyvalues;
            $smartyvalues = self::$params;

            return self::$params;
        };
    }

    public function setInvoices($params)
    {
        $reseller = ResellerHelper::getByCurrentURL();
        $userId = Session::get('uid');
        $isReseller = ResellerHelper::isReseller($userId);
        $isResellerClient = (new ResellersClients())->getByRelid($userId)->exists;

        if ($isReseller || !$isResellerClient) {
            return $params;
        }

        if ($reseller->settings->admin->disableEndClientInvoices) {
            $params["subtotal"] = $params["tax"] = $params["tax2"] = $params["total"] = 0;
            $params["invoiceitems"] = [];
            return $params;
        }
        $resellerClientCase = CasesFactory::getByCurrentURL();

        if ($parentInvoiceId = $resellerClientCase->getParentInvoiceId()) {
            redir("id=" . (int) $parentInvoiceId, "viewinvoice.php");
        }

        $params["invoiceitems"] = $resellerClientCase->getInvoiceItemsFromWhmcsInvoiceItems($params["invoiceitems"]);

        $newPartialPayments = $resellerClientCase->getInvoicesPartialPayments();
        $newCredits = $resellerClientCase->getInvoicesCredit();
        $newSubTotal = $resellerClientCase->getInvoicesSubTotal(array_keys($params["invoiceitems"]));
        $newTotal = $resellerClientCase->getInvoicesTotal(array_keys($params["invoiceitems"]));
        $newTax = $resellerClientCase->getInvoicesTotalTax(array_keys($params["invoiceitems"]));
        $newTax2 = $resellerClientCase->getInvoicesTotalTax2(array_keys($params["invoiceitems"]));

        $params["partialpayments"] = $newPartialPayments ? formatCurrency($newPartialPayments) : 0;
        $params["credit"] = $newCredits ? formatCurrency($newCredits) : 0;
        $params["total"] = $newTotal ? formatCurrency($newTotal) : 0;
        $params["subtotal"] = $newSubTotal ? formatCurrency($newSubTotal) : 0;
        $params["tax"] = $newTax ? formatCurrency($newTax) : 0;
        $params["tax2"] = $newTax2 ? formatCurrency($newTax2) : 0;

        return $params;
    }
}