<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\repository\SessionStorage;
/**
 * Description of ClientChangePassword
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ClientChangePassword 
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
            return $this->storeClientPassword($params);
        };
    }
    
    /**
     * Store decrypted client password in database
     * 
     * @param type $params
     * @return type
     */
    public function storeClientPassword($params)
    {
        $storage = new SessionStorage();
                
        $storageKey = "userid_" . $params["userid"];
        $storage->deleteByKey($storageKey);
        $storage->createNew($storageKey, 1, $params["password"]);
                
        return $params;
    }
}
