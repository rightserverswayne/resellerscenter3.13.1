<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rules;
use MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rule;

/**
 * Description of Active
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Active extends Rule
{
    /**
     * Check if promotion is active
     *
     * @param $product
     * @return bool
     */
    public function run($product)
    {
        $result = true;
        $today  = date("y-m-d");

        if(strtotime($this->promotion->startdate) > 0 && strtotime($today) < strtotime($this->promotion->startdate))
        {
            $result = false;
        }
        elseif(strtotime($this->promotion->expirationdate) > 0 && strtotime($today) > strtotime($this->promotion->expirationdate))
        {
            $result = false;
        }

        return $result;
    }
}