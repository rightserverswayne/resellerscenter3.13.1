<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\ClientLoginHelper;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\Core\Session;

class UserLogin
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     *
     * @var array
     */
    public $functions;

    /**
     * Assign anonymous function
     */
    public function __construct()
    {

        /* Saving Client info for WHMCS8 */
        $this->functions[0] = function($params)
        {
            return $this->setClientSession($params);
        };

        $this->functions[10] = function($params)
        {
            if(!in_array(Request::get('mg-action'), ['returnToRc', 'loginAsClient']) &&
                !(basename(Server::get("SCRIPT_NAME")) == 'upgrade.php' && Request::get('step') == 3))
                return ClientLoginHelper::cleanSessionForAdmin($params);
        };

        $this->functions[20] = function($params)
        {
            if(!in_array(Request::get('mg-action'), ['returnToRc', 'loginAsClient']))
                return ClientLoginHelper::blockMainWhmcs($params);
        };

        $this->functions[30] = function($params)
        {
            //if user is assigned to more clients, wait with authentication to next page
            if(!in_array(Request::get('mg-action'), ['returnToRc', 'loginAsClient']) && $this->getAmountOfUserClients($params) < 2)
                return ClientLoginHelper::authenticateClient($params);
        };
    }

    private function getAmountOfUserClients($params)
    {
        return Whmcs::isVersion('8.0') ? count($params['user']->clients) : 1;
    }

    private function setClientSession($params)
    {
        if(Whmcs::isVersion('8.0')
            && $params['user']->getNumberOfClients() == 1
            && !in_array(Request::get('mg-action'), ['returnToRc', 'loginAsClient'])
        )
        {
            Session::set('RCSelectedAcc', $params['user']->getClientIds()[0]);
        }

        if(Whmcs::isVersion('8.0') && Request::get('mg-action') === 'loginAsClient')
        {
            Session::set('RCSelectedAcc', Request::get('clientid'));
        }

        return $params;
    }
}