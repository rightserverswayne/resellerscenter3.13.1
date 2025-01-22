<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\ClientLoginHelper;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\core\Server;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Session;

class AccountSwitch
{
    public $functions;

    public function __construct()
    {
        $this->functions = [];
    }
    /**
     * Due to WHMCS8 new order checkout payment gateway validation we need to bypass it
     * This hook is for saving choosen gateway, then replacing it with any WHMCS gateway for proper validation
     * 'Hook' is implemented below
     */
}

if(Whmcs::isVersion('8.0')
    && (basename(Server::get('SCRIPT_NAME')) == 'index.php')
    && in_array('/user/accounts', [Request::get('rp'), Server::get('PATH_INFO')])
    && (Request::get('id'))
)
{
    Session::set('RCSelectedAcc', Request::get('id'));

    ClientLoginHelper::cleanSessionForAdmin();
    ClientLoginHelper::blockMainWHMCS();
    ClientLoginHelper::authenticateClient();
}

if(Whmcs::isVersion('8.0')
    && Request::get('mg-action') === 'returnToRc'
)
{
    Session::clear('RCSelectedAcc');
}