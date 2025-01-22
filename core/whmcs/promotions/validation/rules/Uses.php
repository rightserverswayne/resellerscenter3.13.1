<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rules;
use MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rule;

/**
 * Description of Uses
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Uses extends Rule
{
    /**
     * Check if promotion has any uses left
     *
     * @param $product
     * @return bool
     */
    public function run($product)
    {
        $result = true;
        if($this->promotion->maxuses)
        {
            if($this->promotion->uses >= $this->promotion->maxuses)
            {
                $result = false;
            }
        }

        return $result;
    }
}