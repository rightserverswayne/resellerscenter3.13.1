<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers;

use MGModule\ResellersCenter\core\Session as ServerSession;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;

use MGModule\ResellersCenter\core\Server;
use MGModule\ResellersCenter\core\Redirect;
/**
 * Description of Session
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Session 
{
    public function loginAsClient($ruid, $uid)
    {
        ServerSession::set("loggedAsClient", $ruid);
        
        //Login client
        $client = new Client($uid);
        $client->login();
        
        $reseller = new Reseller($ruid);
        $domain = $reseller->settings->private->domain;
        $page   = substr(Server::get("SCRIPT_NAME"), 1);

        //choose redirection "type" - to domain or with param
        if(empty($domain) || !$reseller->settings->admin->cname) 
        {
            $domain = Server::get("HTTP_HOST");
            $query = array("resid" => $reseller->id);
        }
        else
        {
            $key = ServerSession::store();
            $query = array("rctoken" => $key);
        }

        Redirect::to($domain, $page, $query);
    }
}
