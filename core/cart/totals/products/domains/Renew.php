<?php

namespace MGModule\ResellersCenter\Core\Cart\Totals\Products\Domains;

use MGModule\ResellersCenter\Core\Cart\Totals\Products\Domain;
use MGModule\ResellersCenter\core\helpers\DomainHelper;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;

use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use \MGModule\ResellersCenter\Core\Whmcs\Services\Domains\Domain as DomainService;
use MGModule\ResellersCenter\repository\Contents;
use MGModule\ResellersCenter\repository\whmcs\Products;

/**
 * Description of Renew
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Renew extends Domain
{
    /**
     * Domain service
     *
     * @var DomainService
     */
    protected $service;

    /**
     * Create from source
     *
     * @param $params
     * @param Reseller $reseller
     * @return Domain|Renew
     */
    public static function createFromSource($params, Reseller $reseller)
    {
        $service = new DomainService($params["domainid"]);
        $helper  = new DomainHelper($service->domain);

        $domain  = new Renew($helper->getTLD(), $reseller, Contents::TYPE_DOMAIN_RENEW);
        $domain->period     = $params["period"];
        $domain->service    = $service;

        return $domain;
    }

    /**
     * Get array for summarized cart view
     *
     * @return array
     */
    public function getSummarizeCartArray(Currency $currency)
    {
        $pricing    = $this->getPricing($currency);
        $price      = $pricing->getBrandedPrice($this->getStandardizedBillingCycle($this->period));

        $result =
        [
            "domain"                => $this->service->domain,
            "regperiod"             => $this->period,
            "priceBeforeTax"        => $price,
            "dnsmanagement"         => $this->addons["dnsmanagement"],
            "emailforwarding"       => $this->addons["emailforwarding"],
            "idprotection"          => $this->addons["idprotection"],
        ];

        return $result;
    }

    public function getPrices(Currency $currency)
    {
        $addons         = $this->getAddonsPricing($currency);
        $freeType       = $this->getFreeType();
        $billingcycle   = $this->getStandardizedBillingCycle($this->period);

        $extra  = 0;
        $extra += $this->addons["dnsmanagement"]    == "on" ? $addons["dnsmanagement"]   * $this->period : 0;
        $extra += $this->addons["emailforwarding"]  == "on" ? $addons["emailforwarding"] * $this->period : 0;
        $extra += $this->addons["idprotection"]     == "on" ? $addons["idprotection"]    * $this->period : 0;

        $pricing = $this->getPricing($currency)->getBrandedFull();
        $today   =  $freeType ? 0 : ($pricing[$billingcycle] + $extra);

        $pricing = $this->getPricing($currency, Contents::TYPE_DOMAIN_RENEW)->getBrandedFull();
        $renewal = ($freeType == Products::FREE_DOMAIN) ? 0 : ($pricing[$billingcycle] + $extra);

        return
            [
                "today"     => $today,
                "recurring" =>
                    [
                        $this->getRecurringPeriod($this->period) => $renewal
                    ]
            ];
    }
}