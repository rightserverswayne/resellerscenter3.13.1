<?php
namespace MGModule\ResellersCenter\Core\Helpers;

use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\Core\Redirect;

class ClientLogoutHelper
{
    /**
     * Check the addon configuration and block main WHMCS for reseller's clients
     * if option mainWhmcsEnabled is disabled
     *
     * @param type $params
     * @return type
     */
    public static function returnToAdminStore($params)
    {
        $reseller = Reseller::getCurrent();
        $userId = Whmcs::isVersion('8.0') ? Session::getAndClear('RCSelectedAcc') : $params["userid"];
        if($reseller->exists && (Reseller::isReseller($userId) || Session::get("loggedAsClient")))
        {
            Session::clear("resid");
            Session::clear("loggedAsClient");

            Redirect::refresh();
        }

        return $params;
    }
}