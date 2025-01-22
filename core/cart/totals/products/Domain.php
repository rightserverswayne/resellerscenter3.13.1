<?php
namespace MGModule\ResellersCenter\Core\Cart\Totals\Products;
use MGModule\ResellersCenter\core\helpers\DomainHelper;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\Products\Domains\Domain as WhmcsDomain;
use MGModule\ResellersCenter\repository\Contents;
use MGModule\ResellersCenter\repository\whmcs\Pricing;
use MGModule\ResellersCenter\repository\whmcs\Products;

/**
 * Description of Domain
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */

class Domain extends WhmcsDomain
{
    /**
     * @var int
     */
    protected $period;

    /**
     * @var mixed
     */
    protected $addons;

    /**
     * @var string
     */
    protected $name;

    /**
     * Create Domain object
     *
     * @param $params
     * @param Reseller $reseller
     * @return Domain
     */
    public static function createFromSource($params, Reseller $reseller)
    {
        $type   = "domain{$params["type"]}";
        $helper = new DomainHelper($params["domain"]);

        $domain = new Domain($helper->getTLD(), $reseller, $type);
        $domain->name   = $params["domain"];
        $domain->period = $params["regperiod"];
        $domain->addons = [
            "dnsmanagement"     => $params["dnsmanagement"],
            "emailforwarding"   => $params["emailforwarding"],
            "idprotection"      => $params["idprotection"],
        ];

        return $domain;
    }

    /**
     * Get Domain price
     *
     * @param Currency $currency
     * @return array
     * @throws \ReflectionException
     */
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

        return
        [
            "today"     => $today,
            "recurring" =>
            [
                $this->getRecurringPeriod($this->period) => $today
            ]
        ];
    }

    /**
     * Get Domain addons pricing
     *
     * @param Currency $currency
     * @return array
     */
    protected function getAddonsPricing(Currency $currency)
    {
        $repo = new Pricing();

        return
        [
            "idprotection"      => $repo->getPrice(Pricing::TYPE_DOMAINADDONS, 0, $currency->id, "ssetupfee"),
            "dnsmanagement"     => $repo->getPrice(Pricing::TYPE_DOMAINADDONS, 0, $currency->id, "msetupfee"),
            "emailforwarding"   => $repo->getPrice(Pricing::TYPE_DOMAINADDONS, 0, $currency->id, "qsetupfee"),
        ];
    }

    /**
     * Get recurring period name of domain period
     *
     * @param $period
     * @return mixed
     */
    protected function getRecurringPeriod($period)
    {
        $map =
        [
            1 => "annually",
            2 => "biennially",
            3 => "triennially"
        ];

        return $map[$period];
    }

    /**
     * Check products in cart to determinate if provided domain should be free
     *
     * @return string
     * @throws \ReflectionException
     */
    protected function getFreeType()
    {
        $type       = "";
        $products   = Session::get("cart.products");

        if( !is_array($products) )
        {
            return $type;
        }

        foreach($products as $raw)
        {
            if($raw["domain"] != $this->name)
            {
                continue;
            }

            $product = Product::createFromSource($raw, $this->reseller);
            if(!$product->isDomainFree($this->extension))
            {
                continue;
            }

            //Stop checking if for at last for one product the domain is free
            $type = $product->freedomain;
            if($type == Products::FREE_DOMAIN)
            {
                break;
            }
        }

        return $type;
    }
}