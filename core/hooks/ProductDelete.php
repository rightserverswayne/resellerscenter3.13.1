<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\repository\Contents;
use MGModule\ResellersCenter\repository\ResellersPricing;

/**
 * Description of AddonDeleted
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ProductDelete 
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
            return $this->removeFromPricingGroups($params);
        };
    }
    
    /**
     * Remove service relation
     * 
     * @param type $params
     * @return type
     */
    public function removeFromPricingGroups($params)
    {
        $productid = $params["pid"];
        
        $repo = new Contents();
        $repo->deleteReleatedContents($productid, Contents::TYPE_PRODUCT);
        
        $resellerPricing = new ResellersPricing();
        $model = $resellerPricing->getModel();
        $model->where("relid", $productid)->where("type", ResellersPricing::TYPE_PRODUCT)->delete();
    }
}
