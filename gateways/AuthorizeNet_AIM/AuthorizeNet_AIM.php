<?php
namespace MGModule\ResellersCenter\gateways\AuthorizeNet_AIM;
use \MGModule\ResellersCenter\core\resources\gateways\OmnipayGateway;
use \MGModule\ResellersCenter\core\resources\gateways\interfaces\CCGateway;

/** 
 * Description of TwoCheckout
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class AuthorizeNet_AIM extends OmnipayGateway implements CCGateway
{    
    public $adminName = "AuthorizeNet AIM";
    
    public $type = "CC";
        
    public function getHeadOutput()
    {
        return "";
    }
}