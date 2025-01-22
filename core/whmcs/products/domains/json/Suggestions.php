<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Products\domains\json;
use MGModule\ResellersCenter\core\helpers\CartHelper;

use \MGModule\ResellersCenter\repository\ResellersPricing;
use \MGModule\ResellersCenter\Core\Whmcs\Products\domains\Domain as ProductDomain;

/**
 * Description of Suggestions
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Suggestions extends Base
{
    public function getResponse()
    {
        $currencyObj = CartHelper::getCurrency();
        $domain = $this->getWhmcsDomain();
        
        //This var is  required by getSuggestions method
        global $currency;
        $currency = $currencyObj->toArray();
        
        $lookupProvider = \WHMCS\Domains\DomainLookup\Provider::factory();
        $searchResult = $lookupProvider->getSuggestions($domain);
        
        $result = $searchResult->toArray();
        foreach($result as $key => &$suggestion)
        {
            $domain = new ProductDomain($suggestion["tld"], $this->domain->reseller, ResellersPricing::TYPE_DOMAINREGISTER);
            $suggestion["pricing"] = $domain->getDecorator($currencyObj)->getCartPeriodDropdown();

            if(empty($suggestion["pricing"]))
            {
                unset($result[$key]);
                continue;
            }
            
            foreach($suggestion["pricing"] as $period => $prices)
            {
                if($prices["register"]->toNumeric() < 0)
                {
                    unset($suggestion["pricing"][$period]);
                }
            }
        }
       
        return array_values($result);
    }
}
