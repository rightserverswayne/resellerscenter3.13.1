<?php
namespace MGModule\ResellersCenter\core\hooks;
use MGModule\ResellersCenter\repository\ResellersServices;

/**
 * Description of AddonDeleted
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class AddonDeleted 
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
        $relation = $repo->getByTypeAndRelId(ResellersServices::TYPE_ADDON, $params["id"]);
        
        if(!empty($relation))
        {
            $repo->delete($relation->id);
        }
    }
}
