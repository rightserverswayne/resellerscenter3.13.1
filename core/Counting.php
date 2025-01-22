<?php
namespace MGModule\ResellersCenter\core;
use MGModule\ResellersCenter\Addon;

use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\models\whmcs\InvoiceItem;
use MGModule\ResellersCenter\repository\whmcs\Pricing;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;

/**
 * Description of Calculation
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */

abstract class Counting 
{
    const TYPES_NAMESPACE = 'MGModule\\ResellersCenter\\core\\countings\\';
    
    /**
     * Friendly name of the counting type
     * This is basicaly lang key
     * 
     * @var string 
     */
    public $name;
    
    /**
     * Short description of the counting type.
     * Lang Key.
     * 
     * @var string
     */
    public $description;

    /**
     * Configuration
     * 
     * @var form\Form
     */
    protected $configuration;

    /**
     * Get counting Object and set perviously defined values to configuration
     * 
     * @param type $type
     * @return \MGModule\ResellersCenter\core\counting\{$type}
     */
    public static function factory($type, $settings = array())
    {
        $classname = self::TYPES_NAMESPACE . $type;
        $obj = new $classname();
        
        $obj->setConfiguration($settings);
        return $obj;
    }

    /**
     * Get profit calculated from invoice item
     * Profit can be counted only from WHMCS invoice item (!)
     */ 
    abstract public function getProfit(InvoiceItem $item, \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller, $discount = 0);
    
    /**
     * Get configuration in form of an array
     */
    public function getConfiguration()
    {
        $result = array();
        foreach($this->configuration as $item)
        {
            $result[$item->name] = $item->value; 
        }
        
        return $result;
    }
    
    /**
     * Set values in configuration fields
     */
    public function setConfiguration($settings)
    {
        if($settings)
        {
            foreach ($settings as $name => $value) {
                $this->configuration->set($name, $value);
            }
        }
    }
    
    /**
     * Get single setting from configuration
     * 
     * @param type $name
     * @return type
     */
    public function getConfigrationValue($name)
    {
        $result = $this->configuration->get($name);
        return $result;
    }
    
    /**
     * Set single setting in configuration
     * 
     * @param type $name
     * @param type $value
     * @return type
     */
    public function setConfigrationValue($name, $value)
    {
        $result = $this->configuration->set($name, $value);
        return $result;
    }
    
    /**
     * Get html code of loaded configuration
     * if configuration exists
     * 
     * @return type
     */
    public function getConfigurationHTML()
    {
        if(! empty($this->configuration))
        {
            return $this->configuration->getHTML();
        }
        
        return '';
    }
    
    /**
     * Get counting name
     * 
     * @return type
     */
    public function getName()
    {
        $classname = get_class($this);
        $name = str_replace(self::TYPES_NAMESPACE, '', $classname);
        
        return $name;
    }
    
    /**
     * Validate form and get errors
     * 
     * @return type
     */
    public function getValidationErrors()
    {
        if(!empty($this->configuration))
        {
            return $this->configuration->validate();
        }
        
        return array();
    }
    
    /**
     * Get Admin price for provided item
     * 
     * @param InvoiceItem $item
     * @param Reseller $reseller
     * @return type
     */
    public function getAdminPrice(InvoiceItem $item, \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller)
    {
        $currency = new Currency($item->client->currencyObj);

        $service = $item->getProductObj($reseller);
        $pricing = $service->getPricing($currency);
        
        $billingcycle = $item->service->billingcycle == "onetime" ? "monthly" : $item->service->billingcycle;
        $price = $pricing->getAdminPrice($billingcycle);
        
        if($item->type == InvoiceItems::TYPE_SETUP) 
        {
            //use just setup free
            $price = $pricing->getAdminPrice(Pricing::SETUP_FEES[$billingcycle]);
        }
        elseif($item->type == InvoiceItems::TYPE_ADDON) 
        {
            //Add setup fee to item if this is addon registration
            $price += ($item->hostingAddon->regdate == $item->duedate) ? $pricing->getAdminPrice(Pricing::SETUP_FEES[$billingcycle]) : 0;
        }
        
        return $price;
    }
    
    /**
     * Get Reseller price for provided item
     * 
     * @param InvoiceItem $item
     * @param type $reseller
     * @return type
     */
    public function getResellerPrice(InvoiceItem $item, \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller)
    {
        $currency = new Currency($item->client->currencyObj);

        $service = $item->getProductObj($reseller);
        $pricing = $service->getPricing($currency);
        
        //Get price for billing cycle
        $billingcycle = $item->service->billingcycle == "onetime" ? "monthly" : $item->service->billingcycle;
        $price = $pricing->getBrandedPrice($billingcycle);
        
        if($item->type == InvoiceItems::TYPE_SETUP) 
        {
            //use just setup free
            $price = $pricing->getBrandedPrice(Pricing::SETUP_FEES[$billingcycle]);
        }
        elseif($item->type == InvoiceItems::TYPE_ADDON) 
        {
            //Add setup fee to item if this is addon registration
            $price += ($item->hostingAddon->regdate == $item->duedate) ? $pricing->getBrandedPrice(Pricing::SETUP_FEES[$billingcycle]) : 0;
        }
        
        return $price;
    }
    
    /**
     * Get all available counting types.
     * 
     * @return type
     */
    public static function getAvailableCountingTypes()
    {
        $raw = scandir(Addon::I()->getMainDIR() . DS . 'core' . DS . 'countings');

        $types = array_diff($raw, array ('.', '..'));       

        $result = array();
        foreach($types as &$file)
        {
            if (!strpos($file, '.php')) {
                continue;
            }
            $classname = substr($file, 0, -4);
            $type = self::factory($classname);
            
            $result[] = array("name" => $classname, "friendlyName" => $type->name, "description" => $type->description);
        }
        
        return $result;
    }

        /**
     * CUSTOM 3.6.1.2463
     * 
     * Get Reseller Price with config options 
     *
     * @param \MGModule\ResellersCenter\models\whmcs\InvoiceItem $item
     * @param \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller
     *
     * @return type
     */

    public function getFixedResellerPrice(InvoiceItem $item, \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller){

        $currency = new Currency($item->client->currencyObj);

        $service = $item->getProductObj($reseller);

        $pricing = $service->getPricing($currency);

        //Get price for billing cycle
        $billingcycle = $item->service->billingcycle == "onetime" ? "monthly" : $item->service->billingcycle;
        $price = $pricing->getBrandedPrice($billingcycle);

        if ($item->type == InvoiceItems::TYPE_SETUP) {
            //use just setup free
            $price = $pricing->getBrandedPrice(Pricing::SETUP_FEES[$billingcycle]);
            $diff = $item->amount - $price;
            return $price + $diff;
        } elseif ($item->type == InvoiceItems::TYPE_HOSTING
            || $item->type == InvoiceItems::TYPE_ABHOSTING
            || $item->type == InvoiceItems::TYPE_ABHOSTING_ITEM) {
            $diff = $item->amount - $price;
            return $price + $diff;
        }

        return $price;
    }

    /**
     * CUSTOM 3.6.1.2463
     * 
     * Get Admin Price with config options 
     *
     * @param \MGModule\ResellersCenter\models\whmcs\InvoiceItem $item
     * @param \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller
     *
     * @return type
     */
    public function getFixedAdminPrice(InvoiceItem $item, \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller){
        $currency = new Currency($item->client->currencyObj);

        $service = $item->getProductObj($reseller);
        $pricing = $service->getPricing($currency);

        $billingcycle = $item->service->billingcycle == "onetime" ? "monthly" : $item->service->billingcycle;
        $price = $pricing->getAdminPrice($billingcycle);
        $resellerPrice = $pricing->getBrandedPrice($billingcycle);

        if ($item->type == InvoiceItems::TYPE_SETUP) {
            //use just setup free
            $price = $pricing->getAdminPrice(Pricing::SETUP_FEES[$billingcycle]);
            $resellerPrice = $pricing->getBrandedPrice(Pricing::SETUP_FEES[$billingcycle]);
            $diff = $item->amount - $resellerPrice;
            return $price + $diff;
        } elseif ($item->type == InvoiceItems::TYPE_HOSTING) {
            $diff = $item->amount - $resellerPrice;
            return $price + $diff;
        }

        return $price;
    }

}
