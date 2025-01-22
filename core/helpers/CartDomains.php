<?php
namespace MGModule\ResellersCenter\core\helpers;

use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\repository\ResellersPricing;
use MGModule\ResellersCenter\repository\whmcs\Pricing;

use MGModule\ResellersCenter\core\helpers\DomainHelper;

/**
 * Description of CartDomain
 *
 * @author Paweł Złamaniec
 */
class CartDomains
{
    /**
     *
     * @var \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller
     */
    public $reseller;
    
    /**
     *
     * @var \MGModule\ResellersCenter\models\whmcs\Currency 
     */
    public $currency;
    
    /**
     *
     * @var \MGModule\ResellersCenter\models\whmcs\DomainPricing 
     */
    public $domains;
    
    public function setReseller(\MGModule\ResellersCenter\Core\Resources\Resellers\Reseller  $reseller)
    {
        $this->reseller = $reseller;
        
        return $this;
    }
    
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
        
        return $this;
    }
    
    public function addDomain(\MGModule\ResellersCenter\models\whmcs\DomainPricing $domain)
    {
        $this->domains[$domain->extension] = $domain;
        return $this;
    }
    
    public function getRegisterPricing($domainid)
    {
        $pricing = $this->getPrice($domainid, ResellersPricing::TYPE_DOMAINREGISTER);
        return $pricing;
    }
    
    public function getTransferPricing($domainid)
    {
        $pricing = $this->getPrice($domainid, ResellersPricing::TYPE_DOMAINTRANSFER);
        return $pricing;
    }
    
    public function getRenewPricing($domainid)
    {
        $pricing = $this->getPrice($domainid, ResellersPricing::TYPE_DOMAINRENEW);
        return $pricing;
    }
    
    public function getPrice($domainid, $type)
    {
        $repo = new ResellersPricing();
        $fullPricing = $repo->getPricingByRelid($this->reseller->id, $domainid, $type);
        $pricing = $fullPricing[$this->currency->id]["pricing"];
        
        return $pricing;
    }

    /**
     * Decorators
     */
    public function insertRenewalsPricing($renewals)
    {
        if($renewals)
        {
            foreach ($renewals as $key => $domain) {
                $domainid = $this->domains[$domain["tld"]]->id;

                $renewalOptions = array();
                $pricing = $this->getRenewPricing($domainid);
                foreach ($pricing as $period => $price) {
                    $renewalOptions[] = array(
                        "period" => array_search($period, Pricing::DOMAIN_PEROIDS),
                        "price" => formatCurrency($price, $this->currency->id),
                        "rawRenewalPrice" => formatCurrency($price, $this->currency->id)
                    );
                }

                //Sort options by year
                usort($renewalOptions, function ($a, $b) {
                    return $a['period'] < $b['period'] ? -1 : 1;
                });

                if (!empty($renewalOptions)) {
                    //WHMCS is so great!!
                    $renewals[$key]["renewaloptions"] = $renewals[$key]["renewalOptions"] = $renewalOptions;
                } else {
                    unset($renewals[$key]);
                }
            }
        }

        return $renewals;
    }
    
    public function insertCartRenewalsPricing($renewals)
    {
        if($renewals)
        {
            foreach ($renewals as $domainid => $domain) {
                $domainHelper = new DomainHelper($domain["domain"]);
                $domainPriceId = $this->domains[$domainHelper->getTLDWithDot()]->id;

                $period = Pricing::DOMAIN_PEROIDS[$domain["regperiod"]];
                $pricing = $this->getRenewPricing($domainPriceId);

                $renewals[$domainid]["price"] = formatCurrency($pricing[$period], $this->currency->id);
            }
        }
        
        return $renewals;
    }
}
