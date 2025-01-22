<?php

namespace MGModule\ResellersCenter\libs\ResellerClientsCases;

use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\libs\ResellerClientsCases\Cases\ExistsAndResellerInvoices;
use MGModule\ResellersCenter\libs\ResellerClientsCases\Cases\ExistsAndWhmcsInvoices;
use MGModule\ResellersCenter\libs\ResellerClientsCases\Cases\NoExistsAndResellerInvoices;
use MGModule\ResellersCenter\libs\ResellerClientsCases\Cases\NoExistsAndWhmcsInvoices;

class CasesFactory
{
    public static function getByCurrentURL(): AbstractCase
    {
        $reseller = ResellerHelper::getByCurrentURL();

        if ($reseller->exists) {
            return self::getExistCaseFromReseller($reseller);
        } else {
            $client = new Client(Session::get("uid"));
            $reseller = $client->getReseller();
            if ($reseller->exists) {
                return self::getNoExistCaseFromReseller($reseller);
            }
        }

        throw new \Exception("This is not reseller client");
    }

    public static function getExistCaseFromReseller($reseller): ExistsCase
    {
        $class =  $reseller->settings->admin->resellerInvoice ? ExistsAndResellerInvoices::class : ExistsAndWhmcsInvoices::class;
        return new $class();
    }

    public static function getNoExistCaseFromReseller($reseller): NoExistsCase
    {
        $class =  $reseller->settings->admin->resellerInvoice ? NoExistsAndResellerInvoices::class : NoExistsAndWhmcsInvoices::class;
        return new $class();
    }
}