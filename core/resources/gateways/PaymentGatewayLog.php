<?php
namespace MGModule\ResellersCenter\core\resources\gateways;
use MGModule\ResellersCenter\Core\Whmcs\WhmcsObject;

/**
 * Description of PaymentGatewayLog
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class PaymentGatewayLog extends WhmcsObject
{
    protected function getModelClass()
    {
        return "MGModule\ResellersCenter\Repository\PaymentGatewaysLogs";
    }
}