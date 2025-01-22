<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers;

use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\Products\Services;
use MGModule\ResellersCenter\repository\whmcs\Currencies;

use \MGModule\ResellersCenter\repository\Contents as ContentsRepo;

/**
 * Description of Contents
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Contents
{
    /**
     * Reseller object
     *
     * @var Reseller 
     */
    protected $reseller;
    
    /**
     * Configured addons
     *
     * @var Contents\Addons
     */
    protected $addons;
    
    /**
     * Configured domains
     *
     * @var Contents\Domains 
     */
    protected $domains;
    
    /**
     * Configured products
     *
     * @var Contents\Products
     */
    protected $products;
    
    /**
     * 
     * @param \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller
     */
    public function __construct(Reseller $reseller)
    {
        $this->reseller = $reseller;
    }
    
    /**
     * Get content by type object
     * 
     * @param type $name
     * @return \MGModule\ResellersCenter\Core\Resources\Resellers\classname
     */
    public function __get($name)
    {
        if(empty($this->{$name}))
        {
            $classname = __NAMESPACE__."\\Contents\\".$name;
            $this->{$name} = new $classname($this->reseller);
        }
        
        return $this->{$name};
    }

    /**
     * Check if reseller has configured specified content
     *
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        if(empty($this->{$name}))
        {
            $classname = __NAMESPACE__."\\Contents\\".$name;
            $this->{$name} = new $classname($this->reseller);
        }
        
        return !$this->{$name}->isEmpty();
    }

    /**
     * Generate default reseller pricing for all available products in reseller's group
     */
    public function generateDefaultPricing()
    {
        //Get all available currencies
        $repo = new Currencies();
        $currencies = $repo->getAvailableCurrencies();

        //Load all available reseller services
        $services = new Services();
        $services->setReseller($this->reseller)->load();

        //Add pricing for each currency and for all available products
        foreach($currencies as $model)
        {
            $currency = new Currency($model);
            foreach($services->addons as $addon)
            {
                $addon->getPricing($currency)->saveDefaultResellerPricing();
            }

            foreach($services->products as $product)
            {
                $product->getPricing($currency)->saveDefaultResellerPricing();
            }

            foreach($services->domains as $domain)
            {
                $domain->getPricing($currency, ContentsRepo::TYPE_DOMAIN_REGISTER)->saveDefaultResellerPricing();
                $domain->getPricing($currency, ContentsRepo::TYPE_DOMAIN_TRANSFER)->saveDefaultResellerPricing();
                $domain->getPricing($currency, ContentsRepo::TYPE_DOMAIN_RENEW)->saveDefaultResellerPricing();
            }
        }
    }
}
