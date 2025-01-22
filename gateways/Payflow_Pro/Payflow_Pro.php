<?php
namespace MGModule\ResellersCenter\gateways\Payflow_Pro;
use MGModule\ResellersCenter\core\resources\gateways\OmnipayGateway;
use MGModule\ResellersCenter\core\resources\gateways\interfaces\CCGateway;

/** 
 * Description of Payflow_Pro
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Payflow_Pro extends OmnipayGateway implements CCGateway
{
    public $adminName = "Payflow Pro";

    protected static string $sysName = 'payflowpro';
    
    public function getHeadOutput()
    {
        return "";
    }
}