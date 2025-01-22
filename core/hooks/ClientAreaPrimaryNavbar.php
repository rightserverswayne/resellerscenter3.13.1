<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\core\cart\Order\View;
use MGModule\ResellersCenter\Core\Helpers\CartHelper;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\Helpers\LagomIntegration;

class ClientAreaPrimaryNavbar
{
    public $functions;
    public static $params;

    public function __construct()
    {
        $this->functions[10] = function($primaryNavbar) {
            $this->formatLagomPrimaryNavbar($primaryNavbar);
        };
    }

    public function formatLagomPrimaryNavbar($primaryNavbar)
    {
        $reseller = Reseller::getCurrent();
        if (LagomIntegration::hasResellerLagomTemplate($reseller)) {
            $currency   = CartHelper::getCurrency();
            $view       = new View($reseller, $currency);
            $view->setLagomPrimaryNavbar($primaryNavbar);
        }
    }
}