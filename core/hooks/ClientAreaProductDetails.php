<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\DateFormatHelper;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;

class ClientAreaProductDetails
{
    public $functions;

    public static $params;

    public function __construct()
    {
        $this->functions[10] = function($params) {
            self::$params = $this->removeUnpaidInvoiceAlert(self::$params);
        };

        $this->functions[20] = function($params)
        {
            //Fix for WHMCS 7.2.1
            global $smartyvalues;
            $smartyvalues = self::$params;

            return self::$params;
        };
    }

    private function removeUnpaidInvoiceAlert($params)
    {
        $reseller = ResellerHelper::getCurrent();

        if ($reseller->exists && $reseller->settings->admin->resellerInvoice) {
            global $ca;
            $ca->assign('unpaidInvoice', false);
        }

        return $params;
    }

}