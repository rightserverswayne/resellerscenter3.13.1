<?php
namespace MGModule\ResellersCenter\core\hooks;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\repository\whmcs\Clients;

use MGModule\ResellersCenter\core\Session;

/**
 * Description of PreCalculateCartTotals
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class PreCalculateCartTotals 
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     * 
     * @var array 
     */
    public $functions;
    
    /**
     * Assign anonymous function
     */
    public function __construct() 
    {
        $this->functions[10] = function($params) {
            return $this->setCurrencyDuringOrderForClient($params);
        };
    }
    
    /**
     * Change currency in cart summary
     * 
     * @param type $params
     * @return type
     */
    public function setCurrencyDuringOrderForClient($params)
    {
        if(!Reseller::isMakingOrderForClient())
        {
            return $params;
        }
        
        $repo = new Clients();
        $client = $repo->find(Session::get("makeOrderFor"));
        
        global $currency;
        $currency = $client->currencyObj->toArray();
        
        return $params;
    }
}
