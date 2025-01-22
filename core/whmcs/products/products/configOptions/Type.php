<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Products\Products\ConfigOptions;

use MGModule\ResellersCenter\Core\Traits\IsObjectProperty;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;

/**
 * Description of Type
 *
 * @author Paweł Złamaniec
 */
abstract class Type
{
    use IsObjectProperty;

    /**
     * Type constructor.
     * Set config option as a parent
     *
     * @param ConfigOption $option
     */
    public function __construct(ConfigOption $option)
    {
        $this->initIsObjectProperty($option);
    }

    /**
     * Get pricing object
     *
     * @param Currency $currency
     * @return Pricing
     */
    public function getPricing(Currency $currency)
    {
        return new Pricing($this->parent->value, $currency);
    }
}