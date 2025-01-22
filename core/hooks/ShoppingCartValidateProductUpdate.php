<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\models\ResellerPricing;
use MGModule\ResellersCenter\repository\Contents;

class ShoppingCartValidateProductUpdate
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
     * @var array
     */
    public static $params;

    public function __construct()
    {
        $this->functions[10] = function( $params ) {
            return $this->validateBillingCycle($params);
        };
    }

    public function validateBillingCycle( $params )
    {
        $reseller = Reseller::getCurrent();
        if( !$reseller->exists || empty($params['billingcycle']) )
        {
            return;
        }
        $productId = $_SESSION['cart']['products'][$params['i']]['pid'];

        $avaliableBillingCycles = ResellerPricing::where('reseller_id', $reseller->id)
                                                 ->where('type', Contents::TYPE_PRODUCT)
                                                 ->where('relid', $productId)
                                                 ->pluck('billingcycle')->toArray();
        if( !in_array($params['billingcycle'], $avaliableBillingCycles, true) )
        {
            return 'Specified Billing Cycle is not available in Reseller Store';
        }
    }
}