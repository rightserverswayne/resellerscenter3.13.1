<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rules;

use MGModule\ResellersCenter\Core\Cart\Totals\Products\Domain;

use MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rule;

/**
 * Description of Period
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Period extends Rule
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
        if($this->promotion->cycles && $product instanceof Domain)
        {
            $yearless = str_replace("Year", "", $this->promotion->cycles);
            $cycles = explode(",", $yearless);

            if(!in_array($product->period, $cycles))
            {
                $result = false;
            }
        }

        return $result;
    }
}