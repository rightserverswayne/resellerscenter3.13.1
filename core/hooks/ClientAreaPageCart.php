<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Cart\Totals\Products\Domain;
use MGModule\ResellersCenter\Core\Resources\Promotions\Promotion;
use MGModule\ResellersCenter\Core\Traits\AssignSmartyParams;
use MGModule\ResellersCenter\Core\Whmcs\Products\products\Decorator as ProductDecorator;
use MGModule\ResellersCenter\gateways\DeferredPayments\DeferredPayments;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\models\whmcs\Product;
use MGModule\ResellersCenter\repository\Contents;
use MGModule\ResellersCenter\repository\whmcs\Clients;
use MGModule\ResellersCenter\repository\ResellersPricing;
use MGModule\ResellersCenter\Core\Whmcs\Products\products\Product as ProductObject;

use MGModule\ResellersCenter\core\Session;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\core\Redirect;
use MGModule\ResellersCenter\core\Server;

use MGModule\ResellersCenter\Core\Cart\Totals;
use MGModule\ResellersCenter\core\cart\Order\View;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\core\helpers\CartHelper;
use MGModule\ResellersCenter\core\helpers\ClientAreaHelper as CAHelper;
use MGModule\ResellersCenter\repository\whmcs\Pricing;

/**
 * Description of AdminAreaPage
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ClientAreaPageCart 
{

    use AssignSmartyParams;
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     * 
     * @var array 
     */
    public $functions;
    
    /**
     * Container for hook params
     * 
     * @var type 
     */
    public static $params;
    
    /**
     * Assign anonymous function
     */
    public function __construct()
    {

        $this->functions[0] = function($params) {

            self::$params = $this->setCartView($params);
            $this->assignParams(self::$params);
        };

        $this->functions[10] = function($params) {
            self::$params = $this->registerRedirections(self::$params);
            $this->assignParams(self::$params);
        };

        $this->functions[20] = function($params) {
            self::$params = $this->addAddonToCart(self::$params);
            $this->assignParams(self::$params);
        };

        $this->functions[30] = function($params) {
            self::$params = $this->fixDomainPriceInCartView(self::$params);
            $this->assignParams(self::$params);
        };

        $this->functions[40] = function($params) {
            self::$params = $this->adjustCartTotals(self::$params);
            $this->assignParams(self::$params);
        };

        $this->functions[50] = function($params) {
            self::$params = $this->setShortestTransferPeriod(self::$params);
            $this->assignParams(self::$params);
        };

        $this->functions[60] = function($params) {
            self::$params = $this->validatePromotionCode(self::$params);
            $this->assignParams(self::$params);
        };

        $this->functions[70] = function($params) {
            self::$params = $this->overrideAvailableCycles(self::$params);
            $this->assignParams(self::$params);
        };

        $this->functions[200] = function($params)
        {
            self::$params = $this->setClientDetails(self::$params);

            //Fix for WHMCS 7.2.1
            global $smartyvalues;
            $smartyvalues = self::$params;

            $this->assignParams(self::$params);
            return self::$params;
        };
    }
    
    public function setCartView($params)
    {
        $reseller = Reseller::getCurrent();
        if(!$reseller->exists)
        {
            return $params;
        }

        $client     = CartHelper::getCurrentClient();
        $currency   = CartHelper::getCurrency();
        $view       = new View($reseller, $currency);
        
        //Set Categories
        $params["productgroups"] = $view->getCategoriesView();
        $view->setSecondarySidebar($params, $currency);

        //Set addons in order group
        if(isset($params["addons"]) && Request::get("gid") == "addons")
        {
            $addons = $view->getAddonsView($client);
            $params = array_merge($params, $addons);
        }
        
        //Set products in order view
        if(isset($params["products"]) && !empty($params["groupname"]) && !Request::get("a") == "view")
        {
            $params["productGroup"] = $view->setAvailableProductGroup($params["gid"]);
            $params["group_name"] = $params["productGroup"]->name;
            $params["products"] = $view->getProductsView($params["gid"]);

            if(count($params["products"]))
            {
                $params["errormessage"] = "";
            }
        }
        
        //Set product during product configuration
        if(isset($params["productinfo"]) && (Request::get("a") == "confproduct" || Request::get("a") == "cyclechange"))
        {
            $groupId = $params["productinfo"]["gid"];
            $products = $view->getProductsView($groupId);
            foreach($products as $product)
            {
                if($product["pid"] == $params["productinfo"]["pid"])
                {
                    $pricing = $product["pricing"];
                    unset($product["pricing"]);

                    $params["productinfo"] = $product;
                    $params["pricing"] = $pricing;
                }
            }

            $addons = $view->getAddonsView($client, $params["productinfo"]["pid"]);
            $params = array_merge($params, $addons);
        }

        //Set domains in cart
        if(Request::get("domain") || Request::get("a") == "domainoptions" || Request::get("a") == "add")
        {
            $domains = $view->getDomainsView($params["searchResults"]);
            $params = array_merge($params, $domains);
        }

        if (!$reseller->settings->admin->resellerInvoice) {
            if ($params["forceRemoveCreditPayment"]) {
                $params["canUseCreditOnCheckout"] = false;
                $params["applycredit"] = false;
            }
            return $params;
        }

        //Credits
        $params["creditBalance"] = formatCurrency($client->credit, $client->currency);
        if ($params["rawtotal"] <= $params["creditBalance"]->toNumeric() &&
            $reseller->settings->admin->allowCreditPayment &&
            !$params["forceRemoveCreditPayment"])
        {
            $params["canUseCreditOnCheckout"] = 1;
        } else {
            $params["canUseCreditOnCheckout"] = 0;
            $params["applycredit"] = false;
        }

        return $params;
    }
    
    /**
     * Remove content that has been disabled by Admin in Resellers settings
     * 
     * @param type $params
     * @return type
     */
    public function registerRedirections($params)
    {
        $reseller = Reseller::getCurrent();
        if(!$reseller->exists) {
            return $params;
        }
       
        //Make redirections
        if(!$reseller->settings->admin->products && !$reseller->settings->admin->domains)
        {
            if(Request::get("a") != "view") {
                Redirect::query(array("a" => "view"));
            }
        }
        elseif(!$reseller->settings->admin->products)
        {
            if(empty(Request::get("a"))) {
                Redirect::query(array("a" => "add", "domain" => "register"));
            }
        }
        elseif(!$reseller->settings->admin->domains)
        {
            if(Request::get("a") == 'add' && !empty(Request::get("domain"))) {
                Redirect::query();
            }
            
            if(Request::get("gid") == 'renewals') {
                Redirect::query();
            }
        }

        return $params;
    }
    
    /**
     * Add Addon to cart and add addon information to cart view object.
     * This is only needed when reseller is making order for his client
     * 
     * @param type $params
     * @return type
     */
    public function addAddonToCart($params)
    {
        $reseller = Reseller::getCurrent();
        if(!$reseller->exists) {
            return $params;
        }
        
        //Get client currency - this is only useful when reseller is making order for his client
        if(Reseller::isMakingOrderForClient())
        {
            $client = CartHelper::getCurrentClient();
            $currency = CartHelper::getCurrency($client->currency);
            $params["currency"] = $currency->toArray();
        }

        //Show addon in cart view
        if (Request::get("a") == 'view') {

            $repo = new ResellersPricing();
            $currency = $params['currency'];
            foreach ($params["addons"] as &$addon) {
                $fullPricing = $repo->getPricingByRelid($reseller->id, $addon['addonid'], ResellersPricing::TYPE_ADDON);

                $price = $fullPricing[$currency->id]["pricing"][$addon['billingcycle']];
                $setupFeePrice = $fullPricing[$currency->id]["pricing"][Pricing::SETUP_FEES[$addon['billingcycle']]];

                $resellerPrice  = new \WHMCS\View\Formatter\Price($price, $currency);
                $resellerSetupFee  = new \WHMCS\View\Formatter\Price($setupFeePrice, $currency);

                $addon['recurring'] = $price ? $resellerPrice : $addon['recurring'];
                $addon['totaltoday'] = $price ? $resellerPrice : $addon['recurring'];
                $addon['setup'] = $setupFeePrice ? $resellerSetupFee : $addon['setup'];
            }
        }

        return $params;
    }
    
    /**
     * WHMCS hook OrderDomainPricingOverride override domain price in different
     * way then hooks for Addons and Product.
     * In this case WHMCS left old price and only cross it out...
     * And this function fixing it.
     * 
     * @param type $params
     * @return type
     */
    public function fixDomainPriceInCartView($params)
    {
        $reseller = Reseller::getCurrent();
        if (!$reseller->exists)
        {
            return $params;
        }

        $currency = CartHelper::getCurrency();
        if($params["domains"])
        {
            foreach ($params["domains"] as $key => $domainCart) {
                //Remove crossed out domain price
                $domainPriceOverride = Session::get("domainPriceOverride");
                if (is_array($domainPriceOverride) && in_array($domainCart["domain"],$domainPriceOverride )) {
                    $price = $params["domains"][$key]["price"];

                    $start = strpos($price, "<strike>");
                    $stop = strpos($price, "</strike>");
                    $textToDelete = substr($price, $start, $stop + 10);

                    $params["domains"][$key]["price"] = str_replace($textToDelete, '', $price);
                }

                //Get domain params from session
                $args = [];
                if(Session::get("cart.domains"))
                {
                    foreach (Session::get("cart.domains") as $raw) {
                        if ($domainCart["domain"] == $raw["domain"]) {
                            $args = $raw;
                            break;
                        }
                    }
                }

                $domain = Domain::createFromSource($args, $reseller);
                $prices = $domain->getPrices($currency);

                $repo = new ResellersPricing();
                $register = $repo->getPricingByRelid($reseller->id, $domain->id, ResellersPricing::TYPE_DOMAINREGISTER);
                $transfer = $repo->getPricingByRelid($reseller->id, $domain->id, ResellersPricing::TYPE_DOMAINTRANSFER);
                $renew = $repo->getPricingByRelid($reseller->id, $domain->id, ResellersPricing::TYPE_DOMAINRENEW);

                $currencyid = $params["currency"]["id"];
                $pricing = CartHelper::getDomainPricingForCart($register[$currencyid]["pricing"], $transfer[$currencyid]["pricing"], $renew[$currencyid]["pricing"], $params["currency"]);

                //Set renew price
                $params["domains"][$key]["renewprice"] = formatCurrency(array_values($prices["recurring"])[0]);
                $params["domains"][$key]["pricing"] = $pricing;
            }
        }

        return $params;
    }

    /**
     * Fix cart totals display.
     * Calculate and summarize all products and domains
     * to get recurring amount for all billingcycles
     *
     * @param $params
     * @return type
     */
    public function adjustCartTotals($params)
    {
        $reseller = Reseller::getCurrent();
        if(!$reseller->exists)
        {
            return $params;
        }

        //Get client and his currency
        $client     = CartHelper::getCurrentClient();
        $currency   = CartHelper::getCurrency();

        $total = new Totals();
        $total->setReseller($reseller)
                ->setClient($client)
                ->setCurrency($currency)
                ->setTaxRates($params["taxrate"], $params["taxrate2"])
                ->setPromotion($params["promotioncode"]);

        //Add products
        if(Session::get("cart.products"))
        {
            foreach (Session::get("cart.products") as $product) {
                $total->products->addFromSource(Contents::TYPE_PRODUCT, $product);
            }
        }

        //Add addons
        if(Session::get("cart.addons"))
        {
            foreach (Session::get("cart.addons") as $addon) {
                $total->products->addFromSource(Contents::TYPE_ADDON, $addon);
            }
        }

        //Add domain
        if(Session::get("cart.domains"))
        {
            foreach (Session::get("cart.domains") as $domain) {
                $total->products->addFromSource(Contents::getDomainType($domain["type"]), $domain);
            }
        }

        //Add domain renewal
        if(Session::get("cart.renewals"))
        {
            foreach (Session::get("cart.renewals") as $domainid => $regperiod) {
                $total->products->addFromSource(Contents::TYPE_DOMAIN_RENEW, ["domainid" => $domainid, "period" => $regperiod]);
            }
        }

        $basicParams = $params ?: [];
        return array_merge($basicParams, $total->getCartTotal());
    }

    /**
     * Set shortest domain transfer period
     * In modern cart there is no possibility to choose transfer period and WHMCS is using shortest possible
     * This funcion is using reseller pricing to find shortest available period
     * 
     * @param type $params
     * @return type
     */
    public function setShortestTransferPeriod($params)
    {
        $reseller = Reseller::getCurrent();
        if(!$reseller->exists) {
            return $params;
        }
        
        //Skip if not at a=confdomains
        if($params["action"] != "confdomains"){
            return $params;
        }

        if($params["domains"])
        {
            foreach ($params["domains"] as $key => $domain) {
                foreach ($domain["pricing"] as $regperiod => $prices) {
                    if (array_key_exists("transfer", $prices)) {
                        break;
                    }
                }

                $sessionCart = Session::get("cart");
                foreach ($sessionCart["domains"] as $cartKey => $cartDomain) {
                    if ($cartDomain["domain"] == $domain["domain"] && $cartDomain["type"] == "transfer") {
                        $params["domains"][$key]["regperiod"] = $regperiod;
                        $_SESSION["cart"]["domains"][$cartKey]["regperiod"] = $regperiod;
                    }
                }
            }
        }
        
        return $params;
    }
    
    /**
     * Check if promotions are available in reseller store 
     * and reseller validate promo code
     * 
     * @param type $params
     * @return type
     */
    public function validatePromotionCode($params)
    {
        $requestArray = Request::get();
        //Lets validate promotion code - WHMCS is doing this in separate requests
        if (isset($requestArray["validatepromo"])) {
            $reseller   = Reseller::getCurrent();
            $promocode  = Request::get("promocode");

            //If we are in reseller store let's check if promocode is exists in his store
            if ($reseller->exists) {
                $promotion = $reseller->promotions->getByCode($promocode);
                $fullcode  = $promotion->exists ? $promotion->getFullCode() : "";

                //Check the promocode and save errors for later if found
                $errors = SetPromoCode($fullcode);
                if ($errors) {
                    Session::set("RC_PromotionErrors", $errors);
                    Redirect::query(["a" => "view", "promoinvalid" => 1]);
                } else {
                    Redirect::query(["a" => "view", "promovalid" => 1]);
                }
            }
        }

        //Check the outcome of the promotion check and add promo or error as a result (this run in separate request)
        if (Request::get("promoinvalid")) {
            //Discount can be apply by WHMCS (if it exist, but does not belongs to reseller).
            $params["promoerrormessage"] = Session::getAndClear("RC_PromotionErrors");
//            $params = CalcPromoDiscount();
        } elseif(Request::get("promovalid")) {
            //Display success alert
            $params["promoaddedsuccess"] = 1;
        }

        //Remove prefix from promocode that is displayed in cart summary
        $regex = str_replace("#", "[0-9]*", Promotion::PREFIX);
        $params["promotioncode"] = preg_replace("/$regex/", "", $params["promotioncode"], 1);

        return $params;
    }
    
    /**
     * Set client details in cart view at the last step.
     * This is only needed when reseller is making order for a client
     * 
     * @param type $params
     * @return type
     */
    public function setClientDetails($params)
    {
        if(! Reseller::isMakingOrderForClient())
        {
            return $params;
        }
        
        $clientid = Session::get("makeOrderFor");
        
        $repo = new Clients();
        $client = $repo->find($clientid);
        $params["clientsdetails"] = $client->toArray();
        $params["country"] = $params["clientsdetails"]["country"];
        $params["client"] = \WHMCS\User\Client::find($clientid);

        $account = $params["client"];
        $account->id = $params['accounts'][0]['id'];
        $params['accounts'][0] = $account;

        if(count($params['accounts']) > 1)
        {
            /* If user have multiple clients connected in one acc trim all of them except first one */
            $params['accounts'] = collect([$params['accounts'][0]]);
        }

        return $params;
    }

    public function overrideAvailableCycles($params)
    {
        $reseller = Reseller::getCurrent();
        if (!$reseller->exists) {
            return $params;
        }

        $currency = CartHelper::getCurrency();

        if ($params['products'] && !empty($params['allAvailableCycles'])) {
            foreach ($params['products'] as $key => $productRow) {
                $product =  new ProductObject(Product::find($productRow['pid']), Reseller::getByCurrentURL());
                $decorator = new ProductDecorator($product, $currency);

                $params['allAvailableCycles'][$key] = $this->parsePricingToCycles($decorator->getPricing(), $currency);
            }
        }

        if (is_array($params['pricing']['cycles']) && count($params['pricing']['cycles']) == 1) {
            $params['billingcycle'] = array_keys($params['pricing']['cycles'])[0];
        }

        return $params;
    }

    protected function parsePricingToCycles($pricing,$currency):array
    {
        $cycles = [];
        foreach ($pricing['cycles'] as $cycle => $cycleValue) {
            $singleCycle = [];
            $singleCycle['price'] = new \WHMCS\View\Formatter\Price($pricing['rawpricing'][$cycle], $currency->toArray());
            $singleCycle['cycle'] = $cycle;
            $singleCycle['toFullString'] = $cycleValue;
            $cycles[] = $singleCycle;
        }
        return $cycles;
    }
}


/**
 * None of WHMCS hooks is running durring add addon :(
 * This have to be catch like this and only when reseller is making order for his client
 */
if(CAHelper::isCartPage() && Reseller::isMakingOrderForClient()
    && Request::get("a") == "add" && isset($_POST["aid"]))
{
    $_SESSION["cart"]["addons"][] = array(
        "id" => $_POST["aid"],
        "productid" => $_POST["productid"]
    );

    //Redirect to cart view
    Redirect::to(Server::get(Configuration::getCGIHostnameVariableName()), Server::get("SCRIPT_NAME"), array("a" => "view"));
}

global $CONFIG;
    
$parsedConfigSystemUrl  = parse_url($CONFIG["SystemURL"]);
$parsedCurrentSystemUrl = parse_url(Server::getCurrentSystemURL());

if(defined("CLIENTAREA") && $parsedConfigSystemUrl['host'] !== $parsedCurrentSystemUrl['host'])
{ 
    $CONFIG['SystemURL'] = rtrim(Server::getCurrentSystemURL(), '/');
}

if (!empty($_REQUEST['rp']) && $_REQUEST['rp'] == '/cart/account/select' && !empty($_REQUEST['account_id']) && $_REQUEST['account_id'] != 'new') {

    $consolidatedEnable = SettingsManager::isConsolidatedEnableForCurrentReseller(null);

    if ($consolidatedEnable) {
        ob_start(function ($content) {
            $decoded = json_decode($content);
            if (!$decoded) {
                return $content;
            }

            $decoded->canUseCreditOnCheckout = false;

            return json_encode($decoded);
        }, 0, 0);
    }
}

