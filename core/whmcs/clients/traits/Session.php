<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Clients\Traits;

use MGModule\ResellersCenter\Addon;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Session as ServerSession;


/**
 * Description of Session
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
Trait Session
{
    public function login($contactid = null)
    {
        if(Whmcs::isVersion('8.0.0'))
        {
            $client =  \WHMCS\User\Client::where("email", $this->email)->first();
            $class = new \WHMCS\Authentication\AuthManager;
            $class->login($client->owner());
        }
        elseif(Whmcs::isVersion('7.10.0'))
        {
            $hash = (new \WHMCS\Authentication\Client($this->id))->generateClientLoginHash($this->id, $contactid, $this->password,$this->email);
        }
        elseif(Whmcs::isVersion("7.3.0"))
        {
            $auth = new \WHMCS\Authentication\Client($this->id);
            $hash = $auth->generateClientLoginHash($this->id, $contactid, $this->password);
        }
        else
        {
            if(! function_exists("generateClientLoginHash")) 
            {
                require Addon::getWHMCSDIR() . '/includes/clientfunctions.php';
            }
            
            $hash = generateClientLoginHash($this->id, $contactid, $this->password);
        }

        if(!empty($contactid))
        {
            ServerSession::set("cid", $contactid);
        }
        
        ServerSession::set("uid", $this->id);
        ServerSession::set("upw", $hash);
        ServerSession::set("tkval", genRandomVal());
    }
}
