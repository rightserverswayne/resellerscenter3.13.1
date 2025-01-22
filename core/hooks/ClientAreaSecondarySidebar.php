<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\core\cart\Order\View;
use MGModule\ResellersCenter\core\helpers\CartHelper;
use MGModule\ResellersCenter\core\Server;
use MGModule\ResellersCenter\repository\ResellersTickets;

/**
 * Description of ClientAreaSecondarySidebar
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ClientAreaSecondarySidebar
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
        $this->functions[10] = function($sidebars) {
            self::$params = $this->initParams($sidebars);
        };

        $this->functions[20] = function() {
            $this->removeDisabledCartButtons(self::$params);
        };

        $this->functions[30] = function() {
            $this->hideTicketsSystem(self::$params);
        };
    }

    public function initParams($sidebar)
    {
        $params['sidebar'] = $sidebar;
        $params['reseller'] = Reseller::getCurrent();

        return $params;
    }
    
    public function removeDisabledCartButtons($params)
    {
        $reseller = $params['reseller'];
        if (!$reseller->exists) {
            return $params;
        }
        
        if (basename(Server::get("SCRIPT_NAME")) == 'index.php' && basename(Server::get("REQUEST_URI")) == 'index.php') {
            $currency   = CartHelper::getCurrency(); 
            $view       = new View($reseller, $currency);

            //Set Categories
            $view->setSecondarySidebarOnRenew($params['sidebar'], $currency);
        }
        
        if ($reseller->settings->admin->domains) {
            return $params;
        }

        if (\Menu::secondarySidebar()->getChild("Actions")) {
            \Menu::secondarySidebar()->getChild("Actions")->removeChild("Domain Renewals");
            \Menu::secondarySidebar()->getChild("Actions")->removeChild("Domain Registration");
            \Menu::secondarySidebar()->getChild("Actions")->removeChild("Domain Transfer");
        }
        
        if (\Menu::secondarySidebar()->getChild("Client Shortcuts")) {
            \Menu::secondarySidebar()->getChild("Client Shortcuts")->removeChild("Register New Domain");
        }
    }

    public function hideTicketsSystem($params)
    {
        $reseller = $params['reseller'];
        if (!$reseller->exists) {
            return $params;
        }

        $departments = $reseller->settings->admin->ticketDeptids;

        if (!empty($departments)) {
            return $params;
        }

        if (!is_a($params['sidebar'],\WHMCS\View\Menu\Item::class)) {
            return $params;
        }

        $sidebar = $params['sidebar'];
        $support = $sidebar->getChild('Support');

        if (!is_a($support,\WHMCS\View\Menu\Item::class)) {
            return $params;
        }

        foreach ($support->getChildren() as $child) {
            $name = $child->getName();
            if (in_array($name, ResellersTickets::BANNED_TICKET_MENU_ITEMS)) {
                $support->removeChild($name);
            }
        }
        return $params;

    }
}
