<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rules;

use MGModule\ResellersCenter\Core\Cart\Totals\Products\Addon;
use MGModule\ResellersCenter\Core\Cart\Totals\Products\Product;

use MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rule;
use MGModule\ResellersCenter\repository\whmcs\Pricing;

/**
 * Description of BillingCycle
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class BillingCycle  extends Rule
{
    /**
     * Check if promotion applies to product
     *
     * @param $product
     * @return bool
     */
    public function run($product)
    {
        $result = true;
        if($this->promotion->cycles && ($product instanceof Addon || $product instanceof Product))
        {
            $cycles         = explode(",", $this->promotion->cycles);
            $billingcycle   = array_search($product->billingcycle, Pricing::BILLING_CYCLES);
            if(!in_array($billingcycle, $cycles))
            {
                $result = false;
            }
        }

        return $result;
    }
}