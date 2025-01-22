<?php
namespace MGModule\ResellersCenter\core\hooks;
use MGModule\ResellersCenter\repository\ResellersClients;

/**
 * Description of PreDeleteClient
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class PreDeleteClient 
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
            return $this->removeRelation($params);
        };
    }
    
    /**
     * Store decrypted client password in database
     * 
     * @param type $params
     * @return type
     */
    public function removeRelation($params)
    {
        $clients = new ResellersClients();
        $clients->deleteByClientId($params["userid"]);
    }
}
