<?php

namespace MGModule\ResellersCenter\Controllers\Addon\Admin;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;

use MGModule\ResellersCenter\repository\whmcs\Currencies;
use MGModule\ResellersCenter\repository\ResellersClients as ClientsRepo;
use MGModule\ResellersCenter\repository\whmcs\Clients as WHMCSClients;

use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\core\datatable\DatatableDecorator as Datatable;
use MGModule\ResellersCenter\mgLibs\Lang;

use MGModule\ResellersCenter\core\Request;
/**
 * Description of Clients
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Clients extends AbstractController
{
    
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Reseller Details
     */
    public function getNotAssignedJSON()
    {
        $filter = Request::get("term");

        $clients = new WHMCSClients();
        $result = $clients->getAvailableClients($filter, 15);

        return $result;
    }
    
    public function getAsssignedJSON()
    {
        $resellerid = Request::get("resellerid");
        
        $clients = new ClientsRepo();
        $result = $clients->getByResellerId($resellerid);

        return $result;
    }
    
    public function assignToResellerJSON()
    {
        $relid      = Request::get("relid");
        $resellerid = Request::get("resellerid");

        $client = new Client($relid);
        if (!$client->exists) {
            return ["error" => Lang::T('add','clientNotProvided')];
        }

        if ($client->getReseller()->exists) {
            return ["error" => Lang::T('add','alreadyexists')];
        }

        $reseller = new Reseller($resellerid);
        $reseller->clients->assign($relid);

        return ["success" => Lang::T('add','success')];
    }
    
    public function deleteFromResellerJSON()
    {
        $aid  = Request::get("assignationid");

        $client = new ClientsRepo();
        $client->deleteByClientId($aid);

        EventManager::call("clientUnassinged", $aid);
        return array("success" => Lang::T('delete','success'));
    }
    
    public function getAssignedClientsForTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();
        $resellerid = Request::get("resellerid");
        
        //$currencies = new Currencies();
        //$currency = $currencies->getDefault();

        $clients = new ClientsRepo();
        $result = $clients->getAssignedForTable($resellerid, $dtRequest);
        
        $format = array(
            "client_id" => array("link" => array("client_id", "client")),
            "firstname" => array("link" => array("client_id", "client")),
            "lastname"  => array("link" => array("client_id", "client")),
            "companyname" => array("default" => "-"),
            //"income"    => array("prefix" => $currency->prefix, "suffix" => $currency->suffix)
        );
        
        $buttons = array(
            array(
                "type" => "only-icon", 
                "class" => "openDeleteClient btn-danger", 
                "data" => array("clientid" => "client_id"), 
                "icon" => "fa fa-trash-o",
                "tooltip" => Lang::T('table','deleteTooltip')
            ),
        );
        
        $datatable = new Datatable($format, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }
}
