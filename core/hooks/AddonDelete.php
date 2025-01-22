<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\repository\Contents;
use MGModule\ResellersCenter\repository\ResellersPricing;

use MGModule\ResellersCenter\core\Server;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\core\Session;

/**
 * Description of AddonDelete
 *
 * @author Paweł Złamaniec
 */
class AddonDelete 
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

if(basename(Server::get("SCRIPT_NAME")) == 'configaddons.php' && Request::get("action") == 'delete' && !empty(Request::get("id")) && Session::get("adminid"))
{
    $addonid = Request::get("id");

    $repo = new Contents();
    $repo->deleteReleatedContents($addonid, Contents::TYPE_ADDON);

    $resellerPricing = new ResellersPricing();
    $model = $resellerPricing->getModel();
    $model->where("relid", $addonid)->where("type", ResellersPricing::TYPE_ADDON)->delete();
}