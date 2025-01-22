<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Addon;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\Libs\ResellerClientsCases\CasesFactory;
use MGModule\ResellersCenter\repository\ResellersClients;

class ClientAreaPageProductsServices
{
    public $functions;

    public static $params;

    public function __construct()
    {
        $this->functions[10] = function ($params) {
            self::$params = $this->editAvailableServicesList($params);
        };

        $this->functions[200] = function()
        {
            global $smartyvalues;
            $smartyvalues = self::$params;

            return self::$params;
        };
    }

    public function editAvailableServicesList($params)
    {
        $reseller = ResellerHelper::getByCurrentURL();
        if (!$reseller->exists && !Addon::I()->configuration()->adminStoreServiceFilter) {
            return $params;
        }

        $isReseller       = ResellerHelper::isReseller(Session::get('uid'));
        $isResellerClient = (new ResellersClients())->getByRelid(Session::get('uid'))->exists;

        if ($isReseller || !$isResellerClient ) {
            return $params;
        }
        $case = CasesFactory::getByCurrentURL();

        $hostingIds = $case->getServicesIds();

        $params['services'] = array_filter($params['services'], function ($service) use ($hostingIds) {
            return in_array($service['id'], $hostingIds);
        });

        return $params;
    }
}