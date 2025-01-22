<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\repository\Contents;
use MGModule\ResellersCenter\repository\ResellersPricing;

use MGModule\ResellersCenter\core\Server;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\core\Session;

/**
 * Description of DomainPricingDelete
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class DomainPricingDelete 
{
    public $functions;

    public function __construct()
    {
        $this->functions = [];
    }
    /**
     * There is not hook for domain pricing delete
     * 'Hook' is implemented below
     */
}

if(basename(Server::get("SCRIPT_NAME")) == 'configdomains.php' && Request::get("action") == 'delete' && !empty(Request::get("id")) && Session::get("adminid"))
{
    $domainid = Request::get("id");

    $repo = new Contents();
    $repo->deleteReleatedContents($domainid, Contents::TYPE_DOMAIN_REGISTER);
    $repo->deleteReleatedContents($domainid, Contents::TYPE_DOMAIN_TRANSFER);
    $repo->deleteReleatedContents($domainid, Contents::TYPE_DOMAIN_RENEW);

    $resellerPricing = new ResellersPricing();
    $model = $resellerPricing->getModel();
    $model->where("relid", $domainid)->where("type", ResellersPricing::TYPE_DOMAINREGISTER)->delete();
    $model->where("relid", $domainid)->where("type", ResellersPricing::TYPE_DOMAINTRANSFER)->delete();
    $model->where("relid", $domainid)->where("type", ResellersPricing::TYPE_DOMAINRENEW)->delete();
}