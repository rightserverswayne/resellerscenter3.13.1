<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\repository\ResellersTickets;

use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;

/**
 * Description of TicketOpen
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class TicketOpen 
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     * 
     * @var array 
     */
    public $functions;
    
    /**
     * Container for hook params
     * 
     * @var type 
     */
    public static $params;
    
    /**
     * Assign anonymous function
     */
    public function __construct() 
    {
        $this->functions[10] = function($params) {
            self::$params = $this->addRelationToReseller($params);
        };
    }
    
    /**
     * Add Ticket relation to reseller
     * 
     * @param type $params
     * @return type
     */
    public function addRelationToReseller($params)
    {
        $reseller = ResellerHelper::getByCurrentURL();
        if(!$reseller->exists) {
            return $params; 
        }
        
        $repo = new ResellersTickets();
        $repo->createNew($reseller->id, $params["ticketid"]);

        EventManager::call("newTicketRelation", $params["ticketid"], $reseller->id);
        return $params;
    }   
}
