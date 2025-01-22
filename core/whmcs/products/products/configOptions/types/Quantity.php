<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Products\Products\ConfigOptions\Types;

use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\ConfigOptions\Pricing;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\ConfigOptions\Type;

/**
 * Description of Quantity
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Quantity extends Type
{
    public function getPricing(Currency $currency)
    {
        return new Pricing($this->parent->suboptions[0]->id, $currency);
    }
}