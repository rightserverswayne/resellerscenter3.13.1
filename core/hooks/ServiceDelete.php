<?php
namespace MGModule\ResellersCenter\core\hooks;
use MGModule\ResellersCenter\repository\ResellersServices;

/**
 * Description of ServiceDelete
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ServiceDelete 
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
     * Remove service relation
     * 
     * @param type $params
     * @return type
     */
    public function removeRelation($params)
    {
        $repo = new ResellersServices();
        $relation = $repo->getByTypeAndRelId(ResellersServices::TYPE_HOSTING, $params["serviceid"]);
        
        if($relation->exists)
        {
            $repo->delete($relation->id);
        }
    }
}
