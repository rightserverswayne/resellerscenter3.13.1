<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Products\Products;

use MGModule\ResellersCenter\Core\Traits\Iterator;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\ConfigOptions\ConfigOption;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\ConfigOptions\Types\Quantity;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\ConfigOptions\Types\YesNo;
use MGModule\ResellersCenter\Core\Whmcs\Services\Hosting\Hosting;
use MGModule\ResellersCenter\repository\whmcs\ConfigOptions as ConfigOptionsRepo;
use MGModule\ResellersCenter\repository\whmcs\HostingConfigOptions;


/**
 * Description of ConfigOptions
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ConfigOptions implements \Iterator
{
    use Iterator;

    /**
     * Product Object
     *
     * @var Product
     */
    protected $product;

    /**
     * Configurable options that belongs to the product
     *
     * @var array
     */
    protected $options;

    /**
     * ConfigOptions constructor.
     *
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
        $this->load();

        $this->initIteratorSetArrayName("options");
    }

    /**
     * Get configurable option
     *
     * @param $configid
     * @return mixed
     */
    public function get($configid)
    {
        return $this->options[$configid];
    }

    /**
     * Set config options values using data from cart
     *
     * @param $cartOpValues
     */
    public function setCartValues($cartOpValues)
    {
        $cartOptions = [];
        if($cartOpValues)
        {
            foreach ($cartOpValues as $configid => $value) {
                $option = $this->get($configid);
                $option->value = $value;
                $cartOptions[$configid] = $option;
            }
        }
        $this->options = $cartOptions;
    }

    /**
     * Set config option values
     *
     * @param Hosting $hosting
     */
    public function setServiceValues(Hosting $hosting)
    {
        if($this->options)
        {
            foreach ($this->options as $configid => $option) {
                $repo = new HostingConfigOptions();
                $value = $repo->getByRelidAndConfig($hosting->id, $configid);

                $option->value = $option->type instanceof Quantity || $option->type instanceof YesNo ? $value->qty : $value->optionid;
            }
        }
    }

    /**
     * Load Configurable options for the current product
     */
    protected function load()
    {
        $repo    = new ConfigOptionsRepo();
        $options = $repo->getByProduct($this->product->id);

        foreach($options as $option)
        {
            $this->options[$option->id] = new ConfigOption($option);
        }
    }
}
