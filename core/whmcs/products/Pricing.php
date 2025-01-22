<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Products;

use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\Products\domains\Domain;
use MGModule\ResellersCenter\repository\ContentsPricing;
use MGModule\ResellersCenter\repository\ResellersPricing;
use MGModule\ResellersCenter\repository\whmcs\Pricing as WHMCSPricing;

/**
 * Description of Pricing
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Pricing 
{
    private $service;
    
    private $serviceType;
    
    private $currency;
    
    private $contentsPricing;
    
    private $resellerPricing;
    
    private $whmcsPricing;
    
    public function __construct($service, $type, Currency $currency)
    {
        if(!$service instanceof products\Product && !$service instanceof domains\Domain && !$service instanceof addons\Addon)
        {
            throw new Exception("Provided service is not an instance of products\Product or domains\Domain or addons\Addon");
        }

        $this->service = $service;
        $this->serviceType = $type;
        $this->currency = $currency;
        
        $this->contentsPricing = new ContentsPricing();
        $this->resellerPricing = new ResellersPricing();
        $this->whmcsPricing = new WHMCSPricing();
    }
    
    /**
     * Pricing set by Admin
     * 
     * @return type
     */
    public function getAdmin()
    {
        $adminPrices = $this->contentsPricing->getPricing($this->service->content->id);
        return $adminPrices[$this->currency->id];
    }
    
    /**
     * Admin price for provided billing cycle
     * 
     * @param type $billingcycle
     * @return type
     */
    public function getAdminPrice($billingcycle)
    {
        $pricing = $this->getAdmin();
        return $pricing[$billingcycle]["adminprice"];
    }
    
    /**
     * Pricing with setupfees from WHMCS (only if reseller is not override them)
     * 
     * @return type
     */
    public function getBrandedFull()
    {
        $branded = $this->getBranded();
        $whmcs = $this->getWHMCS();

        if( !empty($branded['pricing']) )
        {
            foreach($branded["pricing"] as $billingcycle => $price)
            {
                //skip setupfees
                if(strpos($billingcycle, "setupfee") !== false) {
                    continue;
                }

                //search for setupfee in WHMCS pricing
                $setupfee = substr($billingcycle, 0, 1) . "setupfee";
                if($whmcs->{$setupfee} > 0 && !isset($branded["pricing"][$setupfee]))
                {
                    $branded["pricing"][$setupfee] = $whmcs->{$setupfee};
                }
            }
        }
        
        $ordered = $this->sortPricing($branded["pricing"]);
        
        return $ordered;
    }
    
    /**
     * Pricing set by Reseller - skip setup fee if has not been overridden
     * 
     * @return type
     */
    public function getBranded()
    {
        $pricing = $this->resellerPricing->getPricingByRelid($this->service->reseller->id, $this->service->id, $this->serviceType);
        $result = $pricing[$this->currency->id];
        
        return $result;
    }

    /**
     * Get price for provided billingcycle
     * 
     * @param string $billingcycle
     * @return string
     */
    public function getBrandedPrice($billingcycle)
    {
        $pricing = $this->service->reseller->id ? $this->getBranded() : $this->getWhmcsPricing();
        return $pricing["pricing"][$billingcycle];
    }
    
    /**
     * Pricing form WHMCS
     * 
     * @return type
     */
    public function getWHMCS()
    {
        $pricing = $this->whmcsPricing->getPricingByRelIdAndType($this->service->id, $this->serviceType, $this->currency->id);
        return $pricing;
    }

    public function getWhmcsPricing()
    {
        $pricing['pricing'] = (array)$this->getWHMCS();
        return $pricing;
    }

    /**
     * Save default reseller pricing
     *
     * @throws \MGModule\ResellersCenter\repository\source\RepositoryException
     */
    public function saveDefaultResellerPricing()
    {
        if(empty($this->service->reseller))
        {
            return;
        }

        $pricing = $this->contentsPricing->getPricing($this->service->content->id);
        if(empty($pricing))
        {
            return;
        }

        $toInsert = array();
        foreach($pricing as $currency => $prices)
        {
            foreach($prices as $billingcycle => $values)
            {
                if($values["highestprice"] > 0)
                {
                    $toInsert[$currency][$billingcycle] = $values["highestprice"];
                }
            }
        }

        $this->resellerPricing->savePricing($this->service->reseller->id, $this->service->id, $this->serviceType, $toInsert);
    }
    
    /**
     * Sort pricing
     * 
     * @return type
     */
    private function sortPricing($pricing)
    {
        $ordered = array();
        if ($this->service instanceof products\Product || $this->service instanceof addons\Addon) {
            $billingcycles = array("monthly", "quarterly", "semiannually", "annually", "biennially", "triennially");
            if (!empty($pricing)) {
                $ordered = array_replace(array_flip($billingcycles), $pricing);
            }
        } else { //domain
            return $pricing;
        }
        
        //remove pricing that does not exists 
        foreach ($ordered as $billingcycle => $price) {
            if (empty($pricing[$billingcycle])){
                unset($ordered[$billingcycle]);
            }
        }
        
        return $ordered;
    }
}
