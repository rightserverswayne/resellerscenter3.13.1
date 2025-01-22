<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\ClientLoginHelper;

/**
 * Description of ClientLogin
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ClientLogin 
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
        $this->functions[0] = function($params)
        {
            return ClientLoginHelper::cleanSessionForAdmin($params);
        };

        $this->functions[10] = function($params)
        {
            return ClientLoginHelper::blockMainWhmcs($params);
        };

        $this->functions[20] = function($params)
        {
            return ClientLoginHelper::authenticateClient($params);
        };
    }
}