<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\repository\whmcs\Hostings;
use MGModule\ResellersCenter\repository\whmcs\Pricing;
use MGModule\ResellersCenter\Core\Whmcs\Products\Addons\Addon;

use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\core\helpers\CartHelper;
use MGModule\ResellersCenter\core\helpers\ClientAreaHelper as CAHelper;

/**
 * Description of OrderAddonPricingOverride
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class OrderAddonPricingOverride 
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
        $this->functions[PHP_INT_MAX] = function($params) {
            return $this->setAddonPriceInCart($params);
        };
    }
    
    /**
     * Set correct price for addon in cart
     * 
     * @param type $params
     * @return type
     */
    public function setAddonPriceInCart($params)
    {
        $reseller = Reseller::getCurrent();
        if(!$reseller->exists)
        {
            return $params;
        }
        
        //Use currency from session (client is not logged)        
        $currency = CartHelper::getCurrency();

        $addon = new Addon($params["addonid"], $reseller);
        $pricing = $addon->getPricing($currency)->getBrandedFull();
        
        if(Whmcs::isVersion("7.2.0"))
        {
            $billingcycle = $params["proddata"]["billingcycle"];
            if(!empty($params["serviceid"]))
            {
                $repo = new Hostings();
                $hosting = $repo->find($params["serviceid"]);
                $billingcycle = $hosting->billingcycle;
            }
            
            //Use shortest period if pricing is not found
            if(empty($pricing[$billingcycle])){
                $billingcycle = array_keys($pricing)[0];
            }
        }
        else
        {
            $billingcycle = $addon->billingcycle;
            if($billingcycle == "onetime"){
                $billingcycle = "monthly";
            }
        }
        
        //Set product pricing
        $result = array("recurring" => $pricing[$billingcycle]);
        if(! empty($pricing[Pricing::SETUP_FEES[$billingcycle]])) {
            $result["setup"] = $pricing[Pricing::SETUP_FEES[$billingcycle]];
        }

        return $result;
    }
    
}