<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Cart\Totals;
use MGModule\ResellersCenter\Core\Cart\Totals\Products\Domain;
use MGModule\ResellersCenter\Core\Cart\Totals\Products\Product;
use MGModule\ResellersCenter\Core\Helpers\Files;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\repository\Contents;
use MGModule\ResellersCenter\repository\ResellersPricing;
use MGModule\ResellersCenter\repository\whmcs\Pricing;

use MGModule\ResellersCenter\core\Session;
use MGModule\ResellersCenter\core\helpers\CartHelper;
use MGModule\ResellersCenter\core\helpers\DomainHelper;

use MGModule\ResellersCenter\core\Server;
use MGModule\ResellersCenter\core\Request;

/**
 * Description of OrderDomainPricingOverride
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class OrderDomainPricingOverride 
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     * 
     * @var array 
     */
    public $functions;
    
    /**
     * Assign anonymous function
     */
    public function __construct() 
    {
        $this->functions[PHP_INT_MAX] = function($params) {
            return $this->setDomainPriceInCart($params);
        };
    }

    /**
     * Set correct price for product in cart
     *
     * @param array $params
     *
     * @throws \ReflectionException
     * @return array|string
     */
    public function setDomainPriceInCart($params)
    {
        $reseller = Reseller::getCurrent();
        if(!$reseller->exists)
        {
            return $params;
        }

        /**
         * (!)
         * This override left old price corssed out!
         * There is a fix for it in ClientAreaPageCart::fixDomainPriceInCartView()
         */
        $domainPriceOverride = Session::get("domainPriceOverride");

        if( is_array($domainPriceOverride) && !in_array($params["domain"], $domainPriceOverride) )
        {
            $override = Session::get("domainPriceOverride");
            $override[] = $params["domain"];
            Session::set("domainPriceOverride", $override);
        }

        //Get product to check if domain can be order as free
        $product = null;
        if(Session::get("cart.products"))
        {
            foreach (Session::get("cart.products") as $raw)
            {
                if($params["domain"] == $raw["domain"])
                {
                    $product = Product::createFromSource($raw, $reseller);
                    break;
                }
            }
        }

        //Get domain object
        $currency = CartHelper::getCurrency();
        $helper   = new DomainHelper($params["domain"]);

        $firstPaymentPrice = '0.00';
        $renewPrice        = '0.00';

        if( $product === null || !$product->isDomainFree($helper->getTLDWithDot()) )
        {
            $domain = $reseller->contents->domains->getServiceObject($helper->getTLD(), "domain{$params['type']}");
            $period = Pricing::DOMAIN_PEROIDS[$params['regperiod']];

            $firstPaymentPrice = $domain
                ->getPricing($currency)
                ->getBrandedPrice($period);

            $renewPrice = $domain
                ->getPricing($currency, Pricing::TYPE_DOMAINRENEW)
                ->getBrandedPrice($period);
        }

        return [
            'firstPaymentAmount' => $firstPaymentPrice,
            'recurringAmount'    => $renewPrice
        ];
    }
}

/**
 * STANDARD_CART
 * Domain price override is not working on domain register page in standard cart
 */
$reseller = Reseller::getCurrent();
if($reseller->exists || Reseller::isMakingOrderForClient())
{
    if( (Request::get("a") == 'checkDomain' || Request::get("rp") == "/domain/check") && in_array(Request::get("type"), ['domain', 'spotlight', 'suggestions', 'transfer']))
    {
        $helper = new DomainHelper(Request::get("domain"));
        $tld = $helper->getTLD();

        $domain = empty($tld) ? $reseller->contents->domains->getFirst(ResellersPricing::TYPE_DOMAINREGISTER) : $reseller->contents->domains->getServiceObject($tld, ResellersPricing::TYPE_DOMAINREGISTER);
        $result = $domain->getCartJson(Request::get("domain"), Request::get("type"));

        Server::obClean();
        $response = new \WHMCS\Http\JsonResponse();
        $response->setData(["result" => $result]);
        $response->send();
        \WHMCS\Terminus::getInstance()->doExit();
    }

    //renewal price
    if(Request::get("a") == "updateDomainPeriod")
    {
        if(!function_exists("calcCartTotals"))
        {
            require_once Files::getWhmcsPath("includes", "orderfunctions.php");
        }

        //Set domain period in session
        if(Session::get("cart.domains"))
        {
            foreach (Session::get("cart.domains") as $key => $raw)
            {
                if(Request::get("domain") != $raw["domain"])
                {
                    continue;
                }

                $_SESSION["cart"]["domains"][$key]["regperiod"] = Request::get("period");
            }
        }

        //Add hook just before calculating total
        $hook = new OrderDomainPricingOverride();
        add_hook("OrderDomainPricingOverride", 1, $hook->functions[PHP_INT_MAX]);

        //Get updated cart totals
        $result     = calcCartTotals();
        $taxes      = CartHelper::getTaxes();
        $currency   = CartHelper::getCurrency();

        $cart = new Totals();
        $cart->setReseller($reseller)
            ->setCurrency($currency)
            ->setTaxRates($taxes["tax1"]["rate"], $taxes["tax2"]["rate"]);

        if(Session::get("cart.products"))
        {
            foreach (Session::get("cart.products") as $product) {
                $cart->products->addFromSource(Contents::TYPE_PRODUCT, $product);
            }
        }

        //Add addons
        if(Session::get("cart.addons"))
        {
            foreach (Session::get("cart.addons") as $addon) {
                $cart->products->addFromSource(Contents::TYPE_ADDON, $addon);
            }
        }

        //Add domain
        if(Session::get("cart.domains"))
        {
            foreach (Session::get("cart.domains") as $domain) {
                $cart->products->addFromSource(Contents::getDomainType($domain["type"]), $domain);
            }
        }

        //Add domain renewal
        if(Session::get("cart.renewals"))
        {
            foreach (Session::get("cart.renewals") as $domainid => $regperiod) {
                $cart->products->addFromSource(Contents::TYPE_DOMAIN_RENEW, ["domainid" => $domainid, "period" => $regperiod]);
            }
        }
        $result = array_merge($result, $cart->getCartTotal());

        //Set renewal prices for domains
        foreach($result["domains"] as &$item)
        {
            //Get domain params from session
            $params = [];
            if(Session::get("cart.domains"))
            {
                foreach (Session::get("cart.domains") as $raw) {
                    if ($item["domain"] == $raw["domain"]) {
                        $params = $raw;
                        break;
                    }
                }
            }

            //Get prices
            $domain = Domain::createFromSource($params, $reseller);
            $prices = $domain->getPrices($currency);
            $item["renewprice"] = formatCurrency(array_values($prices["recurring"])[0]);
        }

        Server::obClean();
        $response = new \WHMCS\Http\JsonResponse();
        $response->setData($result);
        $response->send();
        \WHMCS\Terminus::getInstance()->doExit();
    }
}
