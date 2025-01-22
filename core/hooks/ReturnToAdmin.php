<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\core\Server;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\core\Session;

/**
 * Description of AddonDelete
 *
 * @author Paweł Złamaniec
 */
class ReturnToAdmin
{
    public $functions;

    public function __construct()
    {
        $this->functions = [];
    }
    /**
     * There is not hook for addon delete pricing
     * This hook is working for delete Addon - not delete hosting addon!
     * 'Hook' is implemented below
     */
}

if(basename(Server::get("SCRIPT_NAME")) == 'logout.php' && Request::get("returntoadmin") && Session::get("adminid"))
{
    Session::clear("resellerid");
    Session::clear("makeOrderFor");
    Session::clear("orderdetails");
    Session::clear("cart");
}