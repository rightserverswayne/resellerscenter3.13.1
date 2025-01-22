<?php
namespace MGModule\ResellersCenter\Core\Helpers;

use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\repository\whmcs\Clients;
use MGModule\ResellersCenter\repository\whmcs\Currencies;

use MGModule\ResellersCenter\core\Server;
use MGModule\ResellersCenter\core\Session;

/**
 * Description of ClientAreaHelper
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ClientAreaHelper
{
    /**
     * Check if current page is client area page
     * FIX: When WHMCS is using "Full Friendly Rewrite" for Friendly URLs setting
     * url is redirected trough index.php in main WHMCS and CLIENTAREA is defined
     * in Admin Area ...
     *
     * @return boolean
     */
    public static function isClientArea()
    {
        global $customadminpath;
        $dir = $customadminpath ?: "admin";
        $path = explode("/", Server::get("SCRIPT_NAME"));

        if(empty(array_intersect([$dir, "crons"], $path)) && basename(Server::get("SCRIPT_NAME")) != "cron.php" && !in_array(php_sapi_name(), ['cli']) && Server::get("SERVER_ADDR"))
        {
            return true;
        }

        return false;
    }

    /**
     * Check if any client is logged
     * 
     * @return boolean
     */
    public static function isClientLogged()
    {
        if(Session::get("uid")) {
            return true;
        }
         
        return false;
    }
    
    /**
     * Check is current page is cart.php
     * 
     * @return boolean
     */
    public static function isCartPage()
    {
        $script = Server::get("SCRIPT_NAME");
        $current = basename($script);
        if(basename($current) == "cart.php") {
            return true;
        }

        return false;
    }
    
    /**
     * Returns path to logo
     * 
     * @return string
     */
    public static function getLogoPath()
    {
        $path = "modules" .DS. "addons" .DS. "ResellersCenter" .DS. 'storage' .DS. 'logo' . DS;
        
        return $path; 
    }
    
    /**
     * Get logged client
     * 
     * @return type
     */
    public static function getLoggedClient()
    {
        $repo = new Clients();
        $client = $repo->find(Session::get("uid"));
        
        return $client;
    }
}
