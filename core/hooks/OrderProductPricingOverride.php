<?php
namespace MGModule\ResellersCenter\core\hooks;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\Core\Redirect;
use MGModule\ResellersCenter\repository\whmcs\Pricing;

use MGModule\ResellersCenter\core\helpers\CartHelper;
use MGModule\ResellersCenter\core\helpers\ClientAreaHelper as CAHelper;

/**
 * Description of OrderProductPricingOverride
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class OrderProductPricingOverride 
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
            return $this->setProductPriceInCart($params);
        };
    }
    
    /**
     * Set correct price for product in cart
     * 
     * @param type $params
     * @return type
     */
    public function setProductPriceInCart($params)
    {
        $reseller = Reseller::getCurrent();
        if(!$reseller->exists)
        {
            return $params;
        }

        try
        {
            //Use currency from session (client is not logged)
            $currency = CartHelper::getCurrency();

            $product = $reseller->contents->products->{$params["pid"]};
            $result = $product->getPricing($currency)->getBranded();

            //Set product pricing
            $billingcycle = $params["proddata"]["billingcycle"] != 'onetime' ? $params["proddata"]["billingcycle"] : 'monthly';

            return array("recurring" => $result["pricing"][$billingcycle], "setup" => $result["pricing"][Pricing::SETUP_FEES[$billingcycle]]);
        }
        catch (\Exception $exception)
        {
            //Product is not assigned to reseller or pricing has not been found
            foreach($_SESSION["cart"]["products"] as $key => $product)
            {
                if($product["pid"] == $params["pid"])
                {
                    unset($_SESSION["cart"]["products"][$key]);
                }
            }

            Redirect::refresh();
        }
    }
}
