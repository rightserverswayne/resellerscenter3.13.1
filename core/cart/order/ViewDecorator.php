<?php

namespace MGModule\ResellersCenter\Core\Cart\Order;

use MGModule\ResellersCenter\Core\Helpers\TldsCategoriesHelper;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\Decorator as ProductDecorator;
use MGModule\ResellersCenter\Core\Whmcs\Products\Domains\Decorator as DomainDecorator;
use MGModule\ResellersCenter\Core\Whmcs\Products\Addons\Decorator as AddonDecorator;

use MGModule\ResellersCenter\Core\Request;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\repository\whmcs\DynamicTranslations;
use WHMCS\Product\Product as WHMCSProduct;

/**
 * Description of ViewDecorator
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ViewDecorator
{
    public $currency;
    
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
    }
    
    public function parseProductGroups($groups)
    {
        $result = [];
        foreach ($groups as $group) {
            $result[] = array(
                "gid" => $group->id,
                "name" => $group->name
            );
        }
        
        return $result;
    }
    
    public function parseProducts($products)
    {
        $result = [];
        $enabledTranslations = $GLOBALS['CONFIG']['EnableTranslations'];
        if ($enabledTranslations) {
            $translationsRepo = new DynamicTranslations();
            $selectedLanguage = $_SESSION['Language'];
            $productsTranslations = $translationsRepo->getProductsTranslations($selectedLanguage);
        }

        foreach ($products as $product) {
            $decorator = new ProductDecorator($product, $this->currency);

            if ($enabledTranslations) {
                $decorator->setTranslatedParams($productsTranslations[$product->id]);
            }

            $single = $decorator->getGeneralData();
            $single["pricing"] = $decorator->getPricing();
            if (Whmcs::isVersion('8.3')) {
                $single["productUrl"] = $this->getProductPath($product->id);
            }
            $result[] = $single;
        }

        usort($result, function ($a, $b) {
            return $a['order'] > $b['order'];
        });

        return $result;
    }

    public function addDynamicTranslation()
    {

    }
    
    public function parseDomains(\MGModule\ResellersCenter\Core\Resources\Resellers\Contents\Domains $domains)
    {
        global $CONFIG;
        $spotlights = explode(",", $CONFIG["SpotlightTLDs"]);
        
        $result = [];
        $spotlightTlds = [];
        $featuredTlds = [];
        
        foreach($domains as $domain)
        {
            $decorator = new DomainDecorator($domain, $this->currency);
            $result = array_merge_recursive($result, $decorator->getAvailableTlds());
            
            if(in_array($domain->extension, $spotlights))
            {
                $spootlight = $decorator->getSpootlightTld();
                $spotlightTlds[] = $spootlight;
                
                $noDotsTld = str_replace(".", "", $domain->extension);
                if(file_exists(ROOTDIR."/assets/img/tld_logos/{$noDotsTld}.png"))
                {
                    $featuredTlds[] = $spootlight;
                }
            }
            if(Whmcs::isVersion('7.10.0'))
            {
                $pricing['currency']                         = $this->currency->toArray();
                $pricing['pricing'][$domain->extensionNoDot] = $decorator->getOrderViewPricing();
                $result['categoriesWithCounts'] = TldsCategoriesHelper::getCategories($domain->extension);
            }
            elseif(Whmcs::isVersion("7.2.0") && $domain->tld->id)
            {
                $pricing["currency"] = $this->currency->toArray();
                $pricing["pricing"][$domain->extensionNoDot] = $decorator->getOrderViewPricing();

                //Get categories with counts
                foreach($domain->tld->getCategories() as $category)
                {
                    $result["categoriesWithCounts"][$category->category]++;
                }
            }
        }
        
        $result["pricing"] = $pricing;
        $result["spotlightTlds"] = $spotlightTlds;        
        $result["featuredTlds"] = $featuredTlds;
        
        return $result;
    }
    
    /**
     * Domain search in modern cart
     * 
     * @param type $suggestions
     */
    public function parseDomainsSearchResults(\MGModule\ResellersCenter\Core\Resources\Resellers\Contents\Domains $domains, $search)
    {
        $result = [];
        $regoptions = [];
        foreach($search["suggestions"] as $key => $suggestion)
        {
            $current = null;
            $onList = false;
            foreach($domains as $domain)
            {
                if(trim($domain->extension, ".") == $suggestion["tld"]) 
                {
                    $current = $domain;
                    $onList = true;
                }
            }
            
            if(!$onList) 
            {
                unset($search["suggestions"][$key]);
            }
            else 
            {
                $decorator = new DomainDecorator($current, $this->currency);
                $regoptions = $search["suggestions"][$key]["pricing"] = $decorator->getSuggestionPricing();
            }
        }

        //Search for main domain
        $main = $domains->getServiceObject($search["tld"], \MGModule\ResellersCenter\repository\ResellersPricing::TYPE_DOMAINREGISTER);
        $decorator = new DomainDecorator($main, $this->currency);
        $result["regoptions"] = $result["transferoptions"] = $search["pricing"] = $decorator->getSuggestionPricing();
        $result["searchResults"] = $search;
        
        //Cart Boxes
        $result["availabilityresults"][0] = $result["searchResults"];
        $result["availabilityresults"][0]["domain"] = $result["searchResults"]["domainName"];
        $result["availabilityresults"][0]["regoptions"] = $result["regoptions"];
        
        //Transfer pricing 
        $result = array_merge($result, $decorator->getTransferResult());
        
        //Parse for other suggestions
        foreach($search["suggestions"] as $suggestion)
        {
            $sugg = array(
                "domain" => $suggestion["idnDomainName"],
                "status" => $suggestion["status"],
                "regoptions" => $suggestion["pricing"]
            );
            
            $result["othersuggestions"][] = $sugg;
        }
        
        return $result;
    }
    
    public function parseAddons(\MGModule\ResellersCenter\Core\Resources\Resellers\Contents\Addons $addons, Client $client = null, $pid = null)
    {
        $result = ["addons" => []];
        $enabledTranslations = $GLOBALS['CONFIG']['EnableTranslations'];
        if ($enabledTranslations) {
            $translationsRepo = new DynamicTranslations();
            $selectedLanguage = $_SESSION['Language'];
            $addonsTranslations = $translationsRepo->getProductAddonsTranslations($selectedLanguage);
        }

        foreach ($addons as $addon) {
            if (!$addon->showorder && Request::get("a") == "confproduct" && Whmcs::isVersion("7.2.0")) {
                continue;
            }
            
            $pids = explode(",", $addon->packages);
            if ($pid !== null && !in_array($pid, $pids)){
                continue;
            }
            
            $decorator = new AddonDecorator($addon, $this->currency);

            if ($enabledTranslations) {
                $decorator->setTranslatedParams($addonsTranslations[$addon->id]);
            }

            $single = $decorator->getGeneralData();
            if ($client !== null) {
                $single["productids"] = $decorator->getProductIds($client);
            }
            
            if (empty($single["productids"]) && $pid === null) {
                continue;
            }
            
            $result["addons"][] = $single;
        }
        
        $result["noaddons"] = empty($result["addons"]) ? 1 : 0;
        return $result;
    }
    
    public function getWhmcsKnpMenuItem($name, $label, $uri)
    {
        $factory = new \Knp\Menu\MenuFactory();
        $whmcsItem = new \WHMCS\View\Menu\Item(uniqid(), $factory);

        $whmcsItem->setName($name);
        $whmcsItem->setLabel($label);
        $whmcsItem->setUri($uri);
        $whmcsItem->setDisplay(true);

        return $whmcsItem;
    }

    private function getProductPath($productId)
    {
        $product = WHMCSProduct::find($productId);
        $urlRoutePath = $product->getRoutePath();
        $url = parse_url($urlRoutePath);
        return $url['path'].($url['query'] ? '?'.$url['query'] : '');
    }
}