<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Products\domains;

use MGModule\ResellersCenter\Core\Helpers\TldsCategoriesHelper;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\repository\whmcs\Pricing;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;

/**
 * Description of Decorator
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Decorator
{
    private $domain;
    private $currency;
    private $register;
    private $transfer;
    private $renew;

    public function __construct(Domain $domain, Currency $currency)
    {
        $this->domain   = $domain;
        $this->currency = $currency;
        $this->register = $this->domain->getPricing($this->currency, \MGModule\ResellersCenter\repository\ResellersPricing::TYPE_DOMAINREGISTER);
        $this->transfer = $this->domain->getPricing($this->currency, \MGModule\ResellersCenter\repository\ResellersPricing::TYPE_DOMAINTRANSFER);
        $this->renew    = $this->domain->getPricing($this->currency, \MGModule\ResellersCenter\repository\ResellersPricing::TYPE_DOMAINRENEW);
    }

    public function getSuggestionPricing()
    {
        $register = $this->register->getBranded();
        $transfer = $this->transfer->getBranded();
        $renew    = $this->renew->getBranded();

        $result = array();
        for ($period = 1; $period <= 10; $period++)
        {
            $name = Pricing::DOMAIN_PEROIDS[$period];
            if ($register["pricing"][$name])
            {
                $result[$period]["register"] = formatCurrency($register["pricing"][$name], $this->currency->id);
            }

            if ($transfer["pricing"][$name])
            {
                $result[$period]["transfer"] = formatCurrency($transfer["pricing"][$name], $this->currency->id);
            }

            if ($renew["pricing"][$name])
            {
                $result[$period]["renew"] = formatCurrency($renew["pricing"][$name], $this->currency->id);
            }
        }

        return $result;
    }

    public function getCartPeriodDropdown()
    {
        $pricing = $this->getSuggestionPricing();
        foreach ($pricing as $period => $values)
        {
            if (!key_exists("register", $values))
            {
                unset($pricing[$period]);
            }
        }

        return $pricing;
    }

    public function getAvailableTlds()
    {
        $result = array();
        if (!empty($this->register->getBranded()))
        {
            $result["registertlds"] = $result["register"]     = $this->domain->extension;
            $result["tlds"]         = $this->domain->extension;
        }

        if (!empty($this->transfer->getBranded()))
        {
            $result["transfertlds"] = $result["transfer"]     = $this->domain->extension;
        }

        return $result;
    }

    public function getSpootlightTld()
    {
        $register = $this->register->getBranded();
        $shortest = $this->domain->getShortestPeriod($register["pricing"]);

        $result = array(
            "tld"              => $this->domain->extension,
            "tldNoDots"        => str_replace(".", "", $this->domain->extension),
            "period"           => $shortest["period"],
            "group"            => $this->domain->group,
            "groupDisplayName" => Whmcs::lang("domainCheckerSalesGroup.{$this->domain->group}"),
        );

        $result["register"] = formatCurrency($register["pricing"][$shortest["name"]], $this->currency->id);

        $transfer           = $this->transfer->getBranded();
        $shortest           = $this->domain->getShortestPeriod($transfer["pricing"]);
        $result["transfer"] = formatCurrency($transfer["pricing"][$shortest["name"]], $this->currency->id);

        $renew           = $this->renew->getBranded();
        $shortest        = $this->domain->getShortestPeriod($renew["pricing"]);
        $result["renew"] = formatCurrency($renew["pricing"][$shortest["name"]], $this->currency->id);

        return $result;
    }

    /**
     * Modern cart
     * Other domains you might be interested in
     */
    public function getForOtherDomainsSuggestions($domain)
    {
        global $whmcs;

        $input = "<input type='checkbox' name='domains[]' value='{$domain}.{$this->domain->extension}'/>";

        $options  = "";
        $register = $this->register->getBranded();
        foreach ($register["pricing"] as $billingcycle => $price)
        {
            $orderyears = $whmcs->get_lang("orderyears");
            $period     = array_search($billingcycle, Pricing::DOMAIN_PEROIDS);
            $price      = formatCurrency($price, $this->currency->id);

            $options .= "<option value='{$period}'>{$period} {$orderyears} @ {$price}</option>";
        }

        $select = "<select name='domainsregperiod[{$domain}.{$this->domain->extension}]'>{$options}</select>";
        return "<tr><td>{$input}</td><td>{$domain}.{$this->domain->extension}</td><td>{$select}<td><tr>";
    }

    /**
     * Get pricing for domain list in Order View
     * 
     * @since WHMCS72
     * @return type
     */
    public function getOrderViewPricing()
    {
        $catsNames  = array();

        if(Whmcs::isVersion('7.10.0'))
        {
            $catsNames = array_keys(TldsCategoriesHelper::getCategories($this->domain->extension));
        }
        else
        {
            $categories = $this->domain->tld->getCategories();
            foreach ($categories as $cats)
            {
                $catsNames[] = $cats->category;
            }
        }


        //Register prices
        $registerPricing = array();
        foreach ($this->register->getBrandedFull() as $billingcycle => $price)
        {
            $period                   = array_search($billingcycle, Pricing::DOMAIN_PEROIDS);
            $registerPricing[$period] = formatCurrency($price, $this->currency->id);
        }

        //Transfer prices
        $transferPricing = array();
        $transferBranded = $this->transfer->getBrandedFull();

        if( is_array($transferBranded) )
        {
            foreach( $transferBranded as $billingcycle => $price )
            {
                $period                   = array_search($billingcycle, Pricing::DOMAIN_PEROIDS);
                $transferPricing[$period] = formatCurrency($price, $this->currency->id);
            }
        }

        //Register prices
        $renewPricing = array();
        $renewBranded = $this->renew->getBrandedFull();
        if( is_array($renewBranded) )
        {
            foreach( $renewBranded as $billingcycle => $price )
            {
                $period                = array_search($billingcycle, Pricing::DOMAIN_PEROIDS);
                $renewPricing[$period] = formatCurrency($price, $this->currency->id);
            }
        }


        ksort($registerPricing);
        ksort($transferPricing);
        ksort($renewPricing);

        $result = array(
            "categories" => $catsNames,
            "addons"     => array(
                "dns"       => $this->domain->dnsmanagement,
                "email"     => $this->domain->emailforwarding,
                "idprotect" => $this->domain->idprotection,
            ),
            "group"      => $this->domain->group,
            "register"   => $registerPricing,
            "transfer"   => $transferPricing,
            "renew"      => $renewPricing,
        );

        return $result;
    }

    public function getTransferResult()
    {
        $result = array();

        $transfer                = $this->transfer->getBranded();
        $shortest                = $this->domain->getShortestPeriod($transfer["pricing"]);
        $result["transferterm"]  = $shortest["period"];
        $result["transferprice"] = formatCurrency($transfer["pricing"][$shortest["name"]], $this->currency->id);

        return $result;
    }
    
    public function getStandardCartLookupResult()
    {
        $result = [];
        $pricing = $this->getCartPeriodDropdown();
        
        if(! empty($pricing))
        {
            foreach($pricing as $period => $values)
            {
                foreach($values as $key => $price) 
                {
                    $pricing[$period][$key] = (string)$price;
                }
            }
            
            $result["pricing"] = $pricing;
            $result["shortestPeriod"] = array_values($pricing)[0];
        }
        else
        {
            $result["isAvailable"] = false;
            $result["isRegistered"] = false;
        }
        
        return $result;
    }
}
