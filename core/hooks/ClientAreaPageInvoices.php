<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\libs\ResellerClientsCases\CasesFactory;
use MGModule\ResellersCenter\repository\ResellersClients;

class ClientAreaPageInvoices
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
        $userId = Session::get('uid');
        $isReseller       = ResellerHelper::isReseller($userId);
        $isResellerClient = (new ResellersClients())->getByRelid($userId)->exists;

        $reseller = ResellerHelper::getByCurrentURL();

        if ($isReseller || !$isResellerClient) {
            return $params;
        }

        if ($reseller->settings->admin->disableEndClientInvoices) {
            $params['invoices'] = [];
        } else {
            $resellerClientCase = CasesFactory::getByCurrentURL();
            $params['invoices'] = $resellerClientCase->getInvoicesFromWhmcsInvoices($params['invoices']);
        }

        if (!empty($params['invoices']) ) {
            foreach ($params['invoices'] as $key => &$invoiceCart) {
                //Set custom invoice number
                if ($reseller->settings->admin->invoiceBranding && $invoiceCart['branded']->invoicenum) {
                    $params['invoices'][$key]['invoicenum'] = $invoiceCart['branded']->invoicenum;
                }
            }
        }

        return $params;
    }

}