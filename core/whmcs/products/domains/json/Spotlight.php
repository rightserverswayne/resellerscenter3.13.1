<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Products\domains\json;
use MGModule\ResellersCenter\core\helpers\CartHelper;

use \MGModule\ResellersCenter\repository\ResellersPricing;
use \MGModule\ResellersCenter\Core\Whmcs\Products\domains\Domain as ProductDomain;
use MGModule\ResellersCenter\Core\Helpers\Reseller;


/**
 * Description of Spotlight
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Spotlight extends Base
{
    public function getResponse()
    {
        $domain = $this->getWhmcsDomain();
        $spotlights = $this->getSpotlightTlds();
        $currency = CartHelper::getCurrency();

        $lookupProvider = \WHMCS\Domains\DomainLookup\Provider::factory();
        $searchResult = $lookupProvider->checkAvailability($domain, $spotlights);


        
        //change prices
        $result = $searchResult->toArray();
        foreach($result as &$raw)
        {
            $domain = new ProductDomain($raw["tld"], $this->domain->reseller);
            $branded = $domain->getDecorator($currency)->getCartPeriodDropdown();

            if( !empty($branded['pricing']) )
            {
                foreach( $branded["pricing"] as $period => $prices )
                {
                    if( $prices["register"]->toNumeric() < 0 )
                    {
                        unset($branded["pricing"][$period]);
                    }
                }
            }
            
            $raw["pricing"] = $branded;
        }
        
        return $result;
    }
    
    protected function getSpotlightTlds()
    {
        $reseller = Reseller::getCurrent();
        $currency = CartHelper::getCurrency();
        $spotlights = array_filter(explode(",", \WHMCS\Config\Setting::getValue("SpotlightTLDs")), function ($item) {
            return $item;
        });
        
        if(empty($spotlights))
        {
            return [];
        }

        $tlds = [];
        foreach($reseller->contents->domains as $domain)
        {
            if(!in_array($domain->extension, $spotlights))
            {
                continue;
            }
            $pricing = $domain->getPricing($currency, ResellersPricing::TYPE_DOMAINREGISTER);
            if($pricing->getBranded())
            {
                $tlds[] = $domain->extension;
            }
        }
        
        return $tlds;
    }
}
