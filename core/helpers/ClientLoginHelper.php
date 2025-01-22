<?php
namespace MGModule\ResellersCenter\Core\Helpers;

use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\repository\ResellersClients;

use MGModule\ResellersCenter\core\Whmcs\AddonModules\AddonModule;

use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\Core\Redirect;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;

class ClientLoginHelper
{
    /**
     * Clean reseller store info in session if admin is trying to login to a user
     *
     * @param $params
     * @return mixed
     */
    public static function cleanSessionForAdmin($params = [])
    {
        if(Session::get("adminid") && (Session::get("resid") || Session::get("loggedAsClient")))
        {
            Session::clear("resid");
            Session::clear("loggedAsClient");

            /* Execute this only if WHMCS 7.10 or lower */
            if(!Whmcs::isVersion('8.0'))
            {
                Redirect::refresh();
            }
        }

        return $params;
    }

    /**
     * Check the addon configuration and block main WHMCS for reseller's clients
     * if option mainWhmcsEnabled is disabled
     *
     * @param type $params
     * @return type
     */
    public static function blockMainWhmcs($params = [])
    {
//        Skip if there is reseller connected with this shop
        if(ResellerHelper::getCurrent()->exists || isset($_SESSION['adminid']))
        {
            return;
        }

        $isWhmcs8 = Whmcs::isVersion('8.0');

        $clientId = $isWhmcs8 ? Session::get('RCSelectedAcc') : $params['userid'];
        $client = new Client($clientId);
        $reseller = $client->getReseller();

        $module = new AddonModule();
        if($reseller->exists && !$module->mainWhmcsEnabled || $reseller->settings->admin->disallowMainStore)
        {
            Session::clear("uid");
            if($isWhmcs8)
            {
                Session::clear('login_auth_tk');
            }

            $domain = Server::get("HTTP_HOST");
            $script = Server::get("SCRIPT_NAME");

            $page = substr($script, 0, strrpos($script, "/")+1). "clientarea.php";
            $query = array("incorrect" => true);

            setcookie('WHMCSUser', null, -1, '/');

            if(!$isWhmcs8)
            {
                Redirect::to($domain, $page, $query);
            }

            /* FlashMessage with login error for WHMCS 8 and newer */
            $flashMessage = new \WHMCS\FlashMessages();
            $flashMessage->get();
            $flashMessage->add('Sorry, could not log in.', 'error');

        }

        return $params;
    }

    /**
     * Check if client belongs to current reseller shop
     *
     * @param type $params
     * @return type
     */
    public static function authenticateClient($params = [])
    {
        $reseller = ResellerHelper::getCurrent();
        if(!$reseller->exists)
        {
            return;
        }

        //Check if client belong to reseller - if this is a new client then he will be logged in in ClientAdd hook.
        $repo       = new ResellersClients();
        $clientId = Whmcs::isVersion('8.0') ? Session::get('RCSelectedAcc') : $params['userid'];
        $relation   = $repo->getByRelidAndResellerId($clientId, $reseller->id);
        $registered = Session::get("ResellersCenter_ClientRegistered");

        if((empty($relation) || $relation->reseller->id != $reseller->id) && !$registered)
        {
            Session::clear("uid");
            if(Whmcs::isVersion('8.0'))
            {
                Session::clear('login_auth_tk');
            }

            $domain = Server::get("HTTP_HOST");
            $script = Server::get("SCRIPT_NAME");

            $page = substr($script, 0, strrpos($script, "/")+1). "clientarea.php";
            $query = array("incorrect" => true);

            setcookie('WHMCSUser', null, -1, '/');
            Redirect::to($domain, $page, $query);
        }

        return $params;
    }
}