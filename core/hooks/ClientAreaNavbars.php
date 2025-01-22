<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\NavbarHelper;
use MGModule\ResellersCenter\core\cart\Order\View;
use MGModule\ResellersCenter\core\cart\Order\ViewDecorator;

use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\core\helpers\CartHelper;

use MGModule\ResellersCenter\core\Redirect;
use MGModule\ResellersCenter\core\Request;

use MGModule\ResellersCenter\Addon;
use MGModule\ResellersCenter\Helpers\LagomIntegration;
use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\repository\whmcs\ProductGroups;

/**
 * Description of ClientAreaPage
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ClientAreaNavbars
{
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
        $this->functions[10] = function() {
            $this->addResellerAreaButton();
        };

        $this->functions[20] = function() {
            $this->hideDomainsButton();
        };

        $this->functions[30] = function() {
            $this->hideSupportButtons();
        };

        $this->functions[40] = function() {
            $this->hideViewAvailableAddonsButton();
        };

        $this->functions[PHP_INT_MAX] = function() {
            $this->hideProductCategories();
            $this->hideInvoices();
        };
    }

    public function addResellerAreaButton()
    {
        $reseller = Reseller::getLogged();
        if (!$reseller->exists || !$reseller->settings->admin->status || Reseller::isMakingOrderForClient()) {
            return;
        }

        Addon::I();
        Lang::getInstance();

        $primaryNavbar = \Menu::primaryNavbar();

        list($id, $options) = NavbarHelper::getResellerAreaButtonDetails();

        $primaryNavbar->addChild(
            $id,
            $options
        );
    }

    public function hideDomainsButton()
    {
        $reseller = Reseller::getCurrent();
        if (!$reseller->exists || $reseller->settings->admin->domains) {
            return;
        }

        $primaryNavbar = \Menu::primaryNavbar();
        $primaryNavbar->removeChild("Domains");

        //Redirect on action domain
        if (Request::get("action") == "domains") {
            Redirect::query();
        }
    }

    public function hideSupportButtons()
    {
        $reseller = Reseller::getCurrent();
        if (!$reseller->exists) {
            return;
        }

        $primaryNavbar  = \Menu::primaryNavbar();

        if (!$reseller->settings->admin->ticketDeptids) {
            unset($primaryNavbar["Support"]["Tickets"]);
            unset($primaryNavbar["Support"]["Open Ticket"]);
            unset($primaryNavbar["Open Ticket"]);
        }

        if ($reseller->settings->admin->disableKb) {
            unset($primaryNavbar["Knowledgebase"]);
            unset($primaryNavbar["Support"]["Knowledgebase"]);
        }
    }

    public function hideViewAvailableAddonsButton()
    {
        $reseller = Reseller::getCurrent();
        if ($reseller->exists && Reseller::isMakingOrderForClient()) {
            $primaryNavbar  = \Menu::primaryNavbar();
            $services = $primaryNavbar->getChild("Services");

            if ($services) {
                $services->removeChild("View Available Addons");
            }
        }
    }

    public function hideProductCategories()
    {
        $reseller = Reseller::getCurrent();
        if (!$reseller->exists) {
            return;
        }

        $currency = CartHelper::getCurrency();

        $view = new View($reseller, $currency);
        $groups = $view->getAvailableGroups($currency);

        if (LagomIntegration::hasResellerLagomTemplate($reseller)) {
            $this->hideLagomProductCategories($groups);
        } else {
            $this->hideWhmcsProductCategories($groups, $reseller);
        }
    }

    protected function hideLagomProductCategories($availableGroups)
    {
        $groupsRepo = new ProductGroups();
        $allGroups = $groupsRepo->all();
        $allGroupSlugs = [];
        foreach ($allGroups as $basicGroup) {
            $allGroupSlugs[] = $basicGroup->slug;
        }

        $groupSlugs = [];
        foreach ($availableGroups as $group) {
            $groupSlugs[] = $group->slug;
        }

        $primaryNavbar = \Menu::primaryNavbar();

        if (\WHMCS\MarketConnect\MarketConnect::hasActiveServices()) {
            $items = \WHMCS\MarketConnect\MarketConnect::getMenuItems(false);
            foreach ($items as $item) {
                $uriElements = explode('/', $item['uri']);
                $groupSlugs[] = end($uriElements);
            }
        }

        foreach ($primaryNavbar->getChildren() as $primaryNavbarItem) {
            $primaryNavbarItemChildren = $primaryNavbarItem->getChildren();
            foreach ($primaryNavbarItemChildren as $child) {
                $uri = $child->getUri();
                $uriElements = explode('/', $uri);
                if (!empty(array_intersect($allGroupSlugs, $uriElements)) && empty(array_intersect($groupSlugs, $uriElements))) {
                    $primaryNavbarItem->removeChild($child);
                }
            }
        }
    }

    protected function hideWhmcsProductCategories($availableGroups, $reseller)
    {
        $primaryNavbar = \Menu::primaryNavbar();

        $productGroupsDropdown = $primaryNavbar[NavbarHelper::WHMCS_GROUPS_MENU_KEY];

        if (empty($productGroupsDropdown)) {
            return;
        }

        $groupNames = [];
        foreach ($availableGroups as $group) {
            $groupNames[] = $group->name;
        }

        foreach ($productGroupsDropdown as $category => $obj) {
            if (!in_array($category, $groupNames) && $category != "Browse Products Services" && strpos($category, "Shop Divider") === false) {
                unset($productGroupsDropdown[$category]);
            }
        }

        //Add groups
        $offset = 20;

        //Add domains categories
        if ($reseller->settings->admin->domains && !LagomIntegration::hasResellerLagomTemplate($reseller)) {
            $order = count($availableGroups) * 10 + $offset;

            $decorator = new ViewDecorator();
            $item1 = $decorator->getWhmcsKnpMenuItem("Register a New Domain", Whmcs::lang("orderregisterdomain"), "cart.php?a=add&domain=register");
            $item1->setOrder($order + 20);
            $productGroupsDropdown->addChild($item1);

            $item = $decorator->getWhmcsKnpMenuItem("Transfer in a Domain", Whmcs::lang("transferinadomain"), "cart.php?a=add&domain=transfer");
            $item->setOrder($order + 30);
            $productGroupsDropdown->addChild($item);
        }

    }

    public function hideInvoices()
    {
        $reseller = Reseller::getCurrent();
        if(!$reseller->exists)
        {
            return;
        }

        if(!$reseller->settings->admin->disableEndClientInvoices)
        {
            return;
        }

        $primaryNavbar = \Menu::primaryNavbar();
        if(!empty($primaryNavbar["Billing"]))
        {
            $navItem = $primaryNavbar["Billing"]->getChild('My Invoices');
            if (is_null($navItem)) {
                return;
            }
            $primaryNavbar["Billing"]->removeChild('My Invoices');

            $navItem = $primaryNavbar["Billing"]->getChild('Mass Payment');
            if (is_null($navItem)) {
                return;
            }
            $primaryNavbar["Billing"]->removeChild('Mass Payment');
        }
    }
}
