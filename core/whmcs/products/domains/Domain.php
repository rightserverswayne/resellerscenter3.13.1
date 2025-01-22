<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Products\Domains;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\Products\AbstractProduct;

use MGModule\ResellersCenter\Core\Whmcs\Products\Pricing;

use MGModule\ResellersCenter\repository\Contents;
use MGModule\ResellersCenter\repository\whmcs\Pricing as WhmcsPricing;
use MGModule\ResellersCenter\repository\whmcs\DomainPricing;

/**
 * Description of Domain
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Domain extends AbstractProduct
{
    
    /**
     * DomainRegister / DomainTransfer / DomainRenewal
     * 
     * @var string
     */
    private $domainType;

    /**
     * Set model for the object
     *
     * @return string
     */
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\models\whmcs\DomainPricing::class;
    }
        
    /**
     * Load Domain
     * 
     * @param type $id
     */
    public function __construct($idOrTld, \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller = null, $type = null, $model = null)
    {
        if(!is_numeric($idOrTld))
        {
            $domains = new DomainPricing();
            $domain = $domains->getByTld($idOrTld);

            $idOrTld = $domain->id;
        }

        $this->domainType = $type;
        parent::__construct($idOrTld, $reseller, $model);
    }
    
    /**
     * Get pricing object
     * 
     * @param Currency $currency
     * @param type $type
     * @return Pricing
     */
    public function getPricing(Currency $currency, $type = null)
    {
        $this->domainType = $type ?: $this->domainType;
        $this->findAndSetContent($this->domainType);

        return new Pricing($this, $this->domainType, $currency);
    }
    
    /**
     * Get cart response
     * 
     * @param type $search
     * @param type $type
     * @return type
     * @throws \Exception
     */
    public function getCartJson($search, $type)
    {
        if(!in_array($type, ["domain", "spotlight", "suggestions", "transfer"]))
        {
            throw new \Exception("Unable to create json response. Unknown type ({$type}) provided");
        }
        
        //Load object
        $clasname = __NAMESPACE__."\\json\\".ucfirst($type);
        $object = new $clasname($this, $search);
        
        return $object->getResponse();
    }
        
    /**
     * Get shortest available registration period
     * 
     * @param type $pricing
     * @return type
     */
    public function getShortestPeriod($pricing)
    {
        $shortestName = null;
        $shortest = null;
        foreach(WhmcsPricing::DOMAIN_PEROIDS as $period => $name)
        {
            if(isset($pricing[$name]))
            {
                $shortestName = $name;
                $shortest = $period;
                break;
            }
        }
        
        return array("name" => $shortestName, "period" => $period);
    }

    /**
     * Get period as billing cycle
     *
     * @param $billingcycle
     * @return string
     */
    public function getStandardizedBillingCycle($billingcycle)
    {
        return \MGModule\ResellersCenter\repository\whmcs\Pricing::DOMAIN_PEROIDS[$billingcycle];
    }

    /**
     * Return true if tax should be applied to this addon
     *
     * @return int
     */
    public function isTaxable()
    {
        return Whmcs::getConfig("TaxDomains") == "on" ? 1 : 0;
    }

    /**
     * Set content model for loaded service
     */
    protected function findAndSetContent()
    {
        $contents = new Contents();
        $content = $contents->getContentByKeys($this->reseller->group_id, $this->id, $this->domainType);

        $this->content = $content;
    }
}