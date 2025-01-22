<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Products\domains\json;
use MGModule\ResellersCenter\core\helpers\CartHelper;

/**
 * Description of Domain
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Domain extends Base
{
    public function getResponse()
    {
        $currency = CartHelper::getCurrency();
        $whmcs   = $this->getWhmcsResult();

        //Swap prices
        foreach ($whmcs as &$result)
        {
            $branded = $this->domain->getDecorator($currency)->getStandardCartLookupResult();
            $result  = array_merge($result, $branded);
        }

        return $whmcs;
    }

    protected function getWhmcsResult()
    {
        $lookupProvider = \WHMCS\Domains\DomainLookup\Provider::factory();
        $domain         = $this->getWhmcsDomain();

        $searchResult = $lookupProvider->checkAvailability($domain, [$domain->getDotTopLevel()])->toArray();
        return $searchResult;
    }
}
