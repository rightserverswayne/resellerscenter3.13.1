<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\core\Server;

/**
 * Description of ClientAreaHeaderOutput
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ClientAreaHeaderOutput
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
        $this->functions[0] = function($params)
        {
            return $this->removeTicketCCRecipients($params);
        };

        $this->functions[10] = function($params)
        {
            return $this->hideSSO($params);
        };

        $this->functions[PHP_INT_MAX] = function($params)
        {
            return $this->removeGatewayContainer($params);
        };
    }

    /**
     * @return string|void
     */
    public function removeTicketCCRecipients()
    {
        if(basename(Server::get("SCRIPT_NAME")) == "viewticket.php" && Request::get("tid"))
        {
            $script = "<script type='text/javascript'>jQuery(document).ready(function(){ jQuery('#sidebarTicketCc').remove();});</script>";
            return $script;
        }
    }

    /**
     * @return string|void
     */
    public function removeGatewayContainer()
    {
        $reseller = Reseller::getCurrent();
        if($reseller->exists && $reseller->settings->admin->disableEndClientInvoices && basename(Server::get("SCRIPT_NAME")) == "cart.php" && Request::get("a") == "checkout")
        {
            $script = "<script type='text/javascript'>jQuery(document).ready(function(){ jQuery('#paymentGatewaysContainer').remove();});</script>";
            return $script;
        }
    }

    public function hideSSO()
    {
        if (basename(Server::get("SCRIPT_NAME")) == "index.php" &&
            (trim(Server::get("PATH_INFO"), '/') =='login') ||
            trim(Request::get("rp"), '/') == 'login') {

            $reseller = Reseller::getCurrent();
            if ($reseller->exists && $reseller->settings->admin->hideSSO == 'on') {
                global $smarty;
                $smarty->assign('linkableProviders', false);
            }
        }
    }
}
