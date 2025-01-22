<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rules;

use MGModule\ResellersCenter\Core\Whmcs\Products\Addons\Addon;
use MGModule\ResellersCenter\Core\Whmcs\Products\Domains\Domain;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\Product;

use MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rule;

/**
 * Description of AppliesTo
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class AppliesTo extends Rule
{
    /**
     * Check if promotion applies to product
     *
     * @param $product
     * @return bool
     */
    public function run($product)
    {
        $result = false;

        if($product instanceof Product)
        {
            if(in_array($product->id, $this->promotion->appliesto))
            {
                $result = true;
            }
        }
        elseif($product instanceof Addon)
        {
            $addonid = "A".$product->id;
            if(in_array($addonid, $this->promotion->appliesto))
            {
                $result = true;
            }
        }
        elseif($product instanceof Domain)
        {
            $domaintld = "D".$product->extension;
            if(in_array($domaintld, $this->promotion->appliesto))
            {
                $result = true;
            }
        }

        return $result;
    }
}