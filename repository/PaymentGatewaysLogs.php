<?php
namespace MGModule\ResellersCenter\repository;
use MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of PaymentGatewaysLogs
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class PaymentGatewaysLogs extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\PaymentGatewayLog';
    }
}
