<?php

namespace MGModule\ResellersCenter\core\cart\Order;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\Group as ProductGroup;

use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Helpers\LagomIntegration;
use MGModule\ResellersCenter\repository\ResellersSettings;
use \WHMCS\Database\Capsule as DB;
use \Knp\Menu\Util\MenuManipulator;

/**
 * Description of View
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class View
{    
    /**
     * Reseller Object
     *
     * @var \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller
     */
    protected $reseller;
    
    /**
     * 
     * @var \MGModule\ResellersCenter\models\whmcs\Currency
     */
    public $currency;
    
    /**
     * Spot light Tlds that are set in WHMCS. 
     * Currently reseller is unable to set this but it has to be branded!
     * 
     * @var type 
     */
    public $spotlightTlds;

    /**
     * View constructor.
     *
     * @param Reseller $reseller
     * @param Currency $currency
     */
    public function __construct(Reseller $reseller, Currency $currency)
    {
        $reseller->contents->addons->setCurrency($currency);
        $reseller->contents->domains->setCurrency($currency);
        $reseller->contents->products->setCurrency($currency);

        $this->reseller = $reseller;
        $this->currency = $currency;
    }
    
    public function setSecondarySidebar(&$params, $currency)
    {
        //Build secondary sidebar
        $sidebar = &$params["secondarySidebar"];
        $this->rebuildSecondarySideBar($sidebar);
        $showHidden = ((new ResellersSettings())->getSetting('showHidden', ResellersSettings::RESELLERS_DEFAULT_CONFIGURATION_ID) === 'on');
        $categories = $sidebar->getChildren();
        foreach($categories["Categories"] as $category)
        {
            //Remove product groups
            $categories["Categories"]->removeChild($category->getName());
        }
        
        //Add categories to side bar
        $groups = $this->getAvailableGroups($currency);
        foreach($groups as $group)
        {
            $child = $group->getCartItemView();
            if($group->id == $params["gid"])
            {
                $child->setClass("active");
            }

            if( $showHidden || !$group->hidden )
            {
                $categories['Categories']->addChild($child);
            }
        }
        
        //Add Addons category
        if (!ResellerHelper::isMakingOrderForClient()) {
            $factory = new \Knp\Menu\MenuFactory();
            $whmcsItem = new \WHMCS\View\Menu\Item(uniqid(), $factory);
            $whmcsItem->setLabel(Whmcs::lang("cartproductaddons"));
            $whmcsItem->setUri("cart.php?gid=addons");
            if( $params["gid"] == "addons") {
                $whmcsItem->setClass("active");
            }

            $categories["Categories"]->addChild($whmcsItem);
        }

    }
    
    public function setSecondarySidebarOnRenew($sidebar, $currency)
    {
        //Build secondary sidebar
        $this->rebuildSecondarySideBar($sidebar);
        $showHidden = ((new ResellersSettings())->getSetting('showHidden', ResellersSettings::RESELLERS_DEFAULT_CONFIGURATION_ID) === 'on');
        $categories = $sidebar->getChildren();
        foreach($categories["Categories"] as $category)
        {
            //Remove product groups
            $categories["Categories"]->removeChild($category->getName());
        }
        
        //Add categories to side bar
        $groups = $this->getAvailableGroups($currency);
        foreach($groups as $group)
        {
            $child = $group->getCartItemView();

            if( $showHidden || !$group->hidden )
            {
                $categories['Categories']->addChild($child);
            }
        }
        
        //Add Addons category
        $factory = new \Knp\Menu\MenuFactory();
        $whmcsItem = new \WHMCS\View\Menu\Item(uniqid(), $factory);
        $whmcsItem->setLabel(Whmcs::lang("cartproductaddons"));
        $whmcsItem->setUri("cart.php?gid=addons");
        
        $categories["Categories"]->addChild($whmcsItem);
    }

    public function setLagomPrimaryNavbar($primaryNavbar)
    {
        $availableGroups = $this->getAvailableGroups($this->currency);
        $menuAllItems = $primaryNavbar->getChildren();

        $menuItemsName = LagomIntegration::getProductGroupsMenuItems();

        $menuItemsName = array_merge($menuItemsName, ['Store', 'Products']);

        $menuItemsForEdit = array_filter($menuAllItems, function($k) use ($menuItemsName) {
                return in_array($k, $menuItemsName);
        }, ARRAY_FILTER_USE_KEY);

        $showHidden = ((new ResellersSettings())->getSetting('showHidden', ResellersSettings::RESELLERS_DEFAULT_CONFIGURATION_ID) === 'on');

        foreach ($availableGroups as $group) {
            $child = $group->getCartItemView();
            if ( $showHidden || !$group->hidden ) {
                $child->setIcon('fa-ticket ls ls-box');
                foreach ($menuItemsForEdit as $menuItem) {
                    $menuItem->addChild($child);
                }
            }
        }
    }
    
    public function getCategoriesView()
    {
        $groups = $this->getAvailableGroups($this->currency);
        $showHidden = ((new ResellersSettings())->getSetting('showHidden', ResellersSettings::RESELLERS_DEFAULT_CONFIGURATION_ID) === 'on');
        foreach( $groups as $key => $group )
        {
            if( !$showHidden && $group->hidden ) unset($groups[$key]);
        }

        $decorator = new ViewDecorator();
        return $decorator->parseProductGroups($groups);
    }
    
    public function setAvailableProductGroup($currentGroupId)
    {
        $groups = $this->getAvailableGroups($this->currency);
        $showHidden = ((new ResellersSettings())->getSetting('showHidden', ResellersSettings::RESELLERS_DEFAULT_CONFIGURATION_ID) === 'on');
        foreach( $groups as $key => $group )
        {
            if( !$showHidden && $group->hidden && (!isset($_GET['gid']) || (isset($_GET['gid']) && (int)$_GET['gid'] !== $group->id)) )
            {
                unset($groups[$key]);
            }
        }
        $exists = false;
        foreach($groups as $group)
        {
            if($currentGroupId == $group->id)
            {
                $currentid = $group->id;
                $exists = true;
                break;
            }
        }

        if(!$exists) {
            $currentid = $groups[0]->id;
        }

        return \WHMCS\Product\Group::find($currentid);
    }
    
    public function getProductsView($groupId)
    {
        $decorator = new ViewDecorator();
        $decorator->setCurrency($this->currency);
        $showHidden = ((new ResellersSettings())->getSetting('showHidden', ResellersSettings::RESELLERS_DEFAULT_CONFIGURATION_ID) === 'on');
        //Get only for selected group
        $products = [];
        foreach($this->reseller->contents->products as $product)
        {
            if($product->group->id === $groupId && ($showHidden || !$product->hidden))
            {
                $products[] = $product;
            }
        }

        return $decorator->parseProducts($products);
    }
    
    public function getUpgradePackagesView($pid)
    {
        $decorator = new ViewDecorator();
        $decorator->setCurrency($this->currency);

        $upgrades = [];
        if(isset($this->reseller->contents->products->{$pid}))
        {
            //Get only upgrades products that reseller has
            $upgrades = $this->reseller->contents->products->{$pid}->getPossibleUpgrades();
            foreach($upgrades as $key => $upgrade)
            {
                $exits = false;
                foreach($this->reseller->contents->products as $product)
                {
                    if($upgrade->id == $product->id)
                    {
                        $exits = true;
                    }
                }

                if(!$exits)
                {
                    $upgrades->delete($key);
                }
            }
        }

        return $decorator->parseProducts($upgrades);
    }
    
    public function getDomainsView($searchResult = array())
    {
        $decorator = new ViewDecorator();
        $decorator->setCurrency($this->currency);
        $result = $decorator->parseDomains($this->reseller->contents->domains);
        
        if(!empty($searchResult))
        {            
            $search = $decorator->parseDomainsSearchResults($this->reseller->contents->domains, $searchResult);
            $result = array_merge($result, $search);
        }
        
        return $result;
    }
    
    public function getAddonsView(Client $client = null, $pid = null)
    {
        $decorator = new ViewDecorator();
        $decorator->setCurrency($this->currency);
        return $decorator->parseAddons($this->reseller->contents->addons, $client, $pid);
    }

    private function getBrandedProductGroupsIds($currency)
    {
        $gids = [];
        foreach($this->reseller->contents->products as $product)
        {
            $pricing = $product->getPricing($currency);
            $branded = $pricing->getBranded();

            if(!in_array($product->gid, $gids) && !empty($branded))
            {
                $gids[] = $product->gid;
            }
        }

        return $gids;
    }
        
    public function getAvailableGroups($currency)
    {
        /* Getting ID list of the Product Groups which contains branded products */
        $gids = $this->getBrandedProductGroupsIds($currency);
        $showHidden = ((new ResellersSettings())->getSetting('showHidden', ResellersSettings::RESELLERS_DEFAULT_CONFIGURATION_ID) === 'on');

        $result = array();
        foreach ($gids as $gid) {
            $group = new ProductGroup($gid);
            if ($showHidden || !$group->hidden) {
                $result[] = $group;
            }
        }

        if( $GLOBALS['CONFIG']['EnableTranslations'] )
        {
            $selectedLanguage = $_SESSION['Language'];
            $translation      = collect(DB::table('tbldynamic_translations')
                                  ->select('related_id', 'translation')
                                  ->whereIn('related_id', $gids)
                                  ->where('related_type', '=', 'product_group.{id}.name')
                                  ->where('language', '=', $selectedLanguage)
                                  ->pluck('translation', 'related_id'))
                                  ->toArray();

            foreach ( $result as $group ) {
                if ( isset($translation[$group->id]) ) {
                    $group->name = $translation[$group->id];
                }
            }
        }

        return $result;
    }

    /**
     * Function created mainly for Twenty-One WHMCS Template (WHMCS 8.1+)
     * to override those product groups which doesnt contain branded products in reseller store
     *
     * @param $currency
     * @return array
     */
    public function getBrandedWhmcsProductGroups($currency)
    {
        /* Getting ID list of the Product Groups which contains branded products */
        $gids = $this->getBrandedProductGroupsIds($currency);

        if($GLOBALS['CONFIG']['EnableTranslations'])
        {
            $selectedLanguage = $_SESSION['Language'];

            return \WHMCS\Product\Group::whereIn('tblproductgroups.id', $gids)
                                       ->select('tblproductgroups.id','tblproductgroups.slug','tblproductgroups.headline','tblproductgroups.tagline','tblproductgroups.orderfrmtpl','tblproductgroups.disabledgateways','tblproductgroups.hidden','tblproductgroups.order','tblproductgroups.created_at','tblproductgroups.updated_at')
                                       ->addSelect(DB::raw('IFNULL(tbldynamic_translations.translation,tblproductgroups.name) AS name'))
                                       ->leftJoin('tbldynamic_translations', function( $join ) use ( $selectedLanguage ) {
                                           $join->on('tbldynamic_translations.related_id', '=', 'tblproductgroups.id')
                                                ->where('tbldynamic_translations.related_type', '=', 'product_group.{id}.name')
                                                ->where('tbldynamic_translations.language', '=', $selectedLanguage);
                                       })
                                       ->get();
        }
        return \WHMCS\Product\Group::whereIn('id', $gids)->get();
    }
    
    protected function rebuildSecondarySideBar($sidebar)
    {
        //get side bar children
        $children = $sidebar->getChildren();
        
        //Remove all children
        foreach($sidebar as $index => $element)
        {
            $sidebar->removeChild($index);
        }
        
        //Insert Categories on the top
        if(!isset($children["Categories"]))
        {
            $factory = new \Knp\Menu\MenuFactory();
            $whmcsItem = new \WHMCS\View\Menu\Item("Categories", $factory);
            $whmcsItem->setLabel(Whmcs::lang("ordercategories"));
            $whmcsItem->setOrder(0);
            
            $sidebar->addChild($whmcsItem);
        }
        
        //Append rest of the children
        foreach($children as $child)
        {
            $sidebar->addChild($child);
        }
    }
}
