<?php
namespace MGModule\ResellersCenter\Core\Cart\Totals\Products;

use MGModule\ResellersCenter\Core\Helpers\CartHelper;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\ConfigOptions\Types\Quantity;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\Product as WhmcsProduct;
use MGModule\ResellersCenter\repository\whmcs\Pricing;

/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Product extends WhmcsProduct
{
    /**
     * @var string
     */
    protected $billingcycle;

    /**
     * @var mixed
     */
    protected $addons;

    /**
     * Create Product Object
     *
     * @param $params
     * @param Reseller $reseller
     * @return Product
     * @throws \ReflectionException
     */
    public static function createFromSource($params, Reseller $reseller)
    {
        $product = new Product($params["pid"], $reseller);

        //If product does not have any configuration the billing cycle will be null
        $product->billingcycle  = $params["billingcycle"] ?: $product->getTheFirstPossibleBillingCycle(CartHelper::getCurrency());
        $product->addons        = $params["addons"];
        $product->qty           = $params["qty"];
        $product->__get("configOptions")->setCartValues($params["configoptions"]);

        return $product;
    }

    /**
     * Get product prices
     *
     * @param Currency $currency
     * @return array
     */
    public function getPrices(Currency $currency)
    {
        $pricing        = $this->getPricing($currency)->getBrandedFull(); 
        $billingcycle   = $this->getStandardizedBillingCycle($this->billingcycle);
        $configoptions  = $this->getConfigOptionsPrices($currency);
        $setupfee       = $pricing[Pricing::SETUP_FEES[$billingcycle]] + $configoptions[Pricing::SETUP_FEES[$billingcycle]];

        $recurring      = $pricing[$billingcycle] + $configoptions[$billingcycle];
        
        if($this->proratabilling)
        {
            if(!function_exists('getProrataValues')) 
            {
                require_once ROOTDIR.DS.'includes'.DS.'invoicefunctions.php';
            }
            $proratavalues  = \getProrataValues($billingcycle, $recurring, $this->proratadate, $this->proratachargenextmonth, date("d"), date("m"), date("Y"), $_SESSION["uid"]);
            $today          = $proratavalues['amount'];
        }
        else
        {
            $today = $recurring;
        }
        
        return
        [
            "today"     => $today,
            "setupfee"  => $setupfee,
            "recurring" =>
            [
                $billingcycle => $recurring
            ],
        ];
    }

    /**
     * Get config options prices
     *
     * @param Currency $currency
     * @return array
     */
    public function getConfigOptionsPrices(Currency $currency)
    {
        $recurring      = $setupfee = 0;
        $billingcycle   = $this->getStandardizedBillingCycle($this->billingcycle);

        //Summarize prices
        foreach($this->configOptions as $config)
        {
            $qty = $config->type instanceof Quantity ? $config->value : 1;

            $pricing    = $config->type->getPricing($currency);
            $recurring  += $pricing->getPrice($billingcycle) * $qty;
            $setupfee   += $pricing->getPrice(Pricing::SETUP_FEES[$billingcycle]) * $qty;
        }

        return
        [
            $billingcycle                      => $recurring,
            Pricing::SETUP_FEES[$billingcycle] => $setupfee
        ];
    }

    /**
     * Get The first possible billing cycle in specified currency or the product
     *
     * @param Currency $currency
     * @return mixed
     */
    public function getTheFirstPossibleBillingCycle(Currency $currency)
    {
        $pricing        = $this->getPricing($currency)->getBranded();
        $billingcycle   = current(array_keys($pricing["pricing"]));

        return $billingcycle;
    }
}

