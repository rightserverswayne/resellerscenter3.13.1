<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\core\Session;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\core\Server;

use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
/**
 * Description of ClientRegister
 *
 * @author Paweł Złamaniec
 */
class ClientRegister 
{
    public $functions;

    public function __construct()
    {
        $this->functions = [];
    }
    /**
     * There is not hook for client register
     * 'Hook' is implemented below
     */
}

//check if client was successfuly registered and redirected to clientarea.php - There is not hook on client register so we have to use this
if((basename(Server::get("SCRIPT_NAME")) == 'register.php' || basename(Server::get("SCRIPT_NAME")) == 'cart.php') && ((Request::get("email") && Request::get("a") == "checkout") || Request::get("register"))) 
{
    global $CONFIG;

    \MGModule\ResellersCenter\Addon::I();
    $reseller = ResellerHelper::getByCurrentURL();
    if($reseller->exists)
    {
        //Email is send in hook ClientAdd
        Session::set("ResellersCenter_ClientRegistered", 1);
        Session::set("preventSend_Client Signup Email", 1);

        //Handle ToS requirement
        if($reseller->settings->admin->branding)
        {
            if($reseller->settings->private->tos)
            {
                $CONFIG["EnableTOSAccept"] = "on";
                $CONFIG["TermsOfService"]  = $reseller->settings->private->tos;
            }
            else
            {
                $CONFIG["EnableTOSAccept"] = "";
            }
        }
    }
}