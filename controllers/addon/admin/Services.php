<?php
namespace MGModule\ResellersCenter\Controllers\Addon\Admin;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;

use MGModule\ResellersCenter\repository\ResellersServices;
use MGModule\ResellersCenter\repository\whmcs\Currencies;
use MGModule\ResellersCenter\repository\whmcs\Hostings;
use MGModule\ResellersCenter\repository\whmcs\Domains;
use MGModule\ResellersCenter\repository\whmcs\HostingAddons;
use MGModule\ResellersCenter\repository\whmcs\Pricing;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\core\datatable\DatatableDecorator as Datatable;
use MGModule\ResellersCenter\mgLibs\Lang;

use MGModule\ResellersCenter\core\Request;
/**
 * Description of Services
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Services extends AbstractController
{
    
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Reseller Details
     */
    
    public function getNotAssignedJSON()
    {
        $type   = Request::get("type");
        $filter = Request::get("term");

        if($type == ResellersServices::TYPE_ADDON)
        {
            $addons = new HostingAddons();
            $all = $addons->getNotAssigned($filter, 15);
        }
        elseif($type == ResellersServices::TYPE_DOMAIN)
        {
            $domains = new Domains();
            $all = $domains->getNotAssigned($filter, 15);
        }
        elseif($type == ResellersServices::TYPE_HOSTING)
        {
            $hosting = new Hostings();
            $all = $hosting->getNotAssigned($filter, 15);
        }
        
        return $all;
    }
    
    public function assignToResellerJSON()
    {
        $resellerid = Request::get("resellerid");
        $relid      = Request::get("relid");
        $type       = Request::get("type");

        if(empty($relid))
        {
            return ["error" => Lang::T('add','serviceNotProvided')];
        }

        $service = new ResellersServices();
        $service->createNew($resellerid, $relid, $type);
     
        EventManager::call("serviceAssignedToReseller", $type, $relid, $resellerid);
        return array("success" => Lang::T('add','success'));
    }
    
    public function reassignToClientJSON()
    {
        $serviceid  = Request::get("serviceid");
        $clientid   = Request::get("clientid");
        
        //Change service in WHMCS
        $ra = new ResellersServices();
        $service = $ra->find($serviceid);
        if($service->type == ResellersServices::TYPE_DOMAIN) 
        {
            $domain = new Domains();
            $domain->reassign($service->relid, $clientid);
        }
        elseif($service->type == ResellersServices::TYPE_HOSTING) 
        {
            $hosting = new Hostings();
            $hosting->reassign($service->relid, $clientid);
        }
        
        EventManager::call("serviceReassingedToClient", $serviceid, $clientid, $service->reseller_id);
        return array("success" => Lang::T('reassign','success'));
    }
    
    public function deleteFromResellerJSON()
    {
        $aid  = Request::get("assignationid");

        $service = new ResellersServices();
        $service->delete($aid);
        
        EventManager::call("serviceUnassingedFromReseller", $aid);
        return array("success" => Lang::T('delete','success'));
    }
    
    public function isResellerServiceJSON()
    {
        $serviceid = Request::get("serviceid");
        
        $rs = new ResellersServices();
        $service = $rs->getByRelId($serviceid, 'hosting');
        
        return $service->exists === true;
    }
    
    public function getServiceDetailsJSON()
    {
        $serviceid = Request::get("serviceid");
        
        $rs = new ResellersServices();
        $service = $rs->find($serviceid);
        if($service->type == ResellersServices::TYPE_ADDON) 
        {
            $addon = new HostingAddons();
            $result = $addon->find($service->relid);
        }
        elseif($service->type == ResellersServices::TYPE_DOMAIN) 
        {
            $domain = new Domains();
            $result = $domain->find($service->relid);
        }
        elseif($service->type == ResellersServices::TYPE_HOSTING) 
        {
            $hosting = new Hostings();
            $result = $hosting->find($service->relid);
        }
        
        $result->type = $service->type;
        return $result;
    }
    
    public function updatePricingJSON()
    {
        $serviceid = Request::get("serviceid");
        $price = Request::get("price");
        $billingcycle = Request::get("billingcycle");
        $registrationperiod = Request::get("registrationperiod");
        
        $ra = new ResellersServices();
        $service = $ra->find($serviceid);
        if($service->type == ResellersServices::TYPE_ADDON) 
        {
            $addon = new HostingAddons();
            $result = $addon->update($service->relid, array("recurring" => $price, "billingcycle" => array_search($billingcycle, Pricing::BILLING_CYCLES)));
        }
        elseif($service->type == ResellersServices::TYPE_DOMAIN) 
        {
            $domain = new Domains();
            $result = $domain->update($service->relid, array("recurringamount" => $price, "registrationperiod" => $registrationperiod));
        }
        elseif($service->type == ResellersServices::TYPE_HOSTING) 
        {
            $hosting = new Hostings();
            $result = $hosting->update($service->relid, array("amount" => $price, "billingcycle" => array_search($billingcycle, Pricing::BILLING_CYCLES)));
        }
        
        EventManager::call("servicePricingUpdated", $serviceid, $price, $billingcycle);
        return array("success" => Lang::T('update','success'));
    }
    
    public function getAssignedHostingForTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();
        $resellerid = Request::get("resellerid");
        
        $reseller = new Reseller($resellerid);
        $result = $reseller->hosting->getForTable($dtRequest);
        
        $format = array(
            "id"        => array("link" => array("hosting_id", "hosting")),
            "name"      => array("link" => array("product_id", "product")),
            "domain"    => array("link" => array("hosting_id", "hosting")),
            "client"    => array("link" => array("client_id", "client")),
        );
        
        $datatable = new Datatable($format, $this->getButtonsForServiceTable());
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }
    
    public function getAssignedAddonsForTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();
        $resellerid = Request::get("resellerid");
        
        $reseller = new Reseller($resellerid);
        $result = $reseller->addons->getForTable($dtRequest);
        
        $format = array(
            "id"        => array("link" => array("hosting_id", "hosting")),
            "name"      => array("link" => array("addon_id", "addon")),
            "domain"    => array("link" => array("hosting_id", "hosting")),
            "client"    => array("link" => array("client_id", "client")),
        );
        
        $buttons = array(
            array("type" => "only-icon", "class" => "openConfigService btn-primary", "data" => array("serviceid" => "id"), "icon" => "fa fa-wrench"),    
            array("type" => "only-icon", "class" => "openDeleteService btn-danger", "data" => array("serviceid" => "id"), "icon" => "fa fa-trash-o"),    
        );
        
        $datatable = new Datatable($format, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }
    
    public function getAssignedDomainsForTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();
        $resellerid = Request::get("resellerid");
        
        $reseller = new Reseller($resellerid);
        $result = $reseller->domains->getForTable($dtRequest);
        
        $format = array(
            "id"        => array("link" => array("domain_id", "domain")),
            "domain"    => array("link" => array("domain_id", "domain")),
            "client"    => array("link" => array("client_id", "client")),
        );
        
        $datatable = new Datatable($format, $this->getButtonsForServiceTable());
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }
   
    private function getButtonsForServiceTable()
    {
        return array(
            array(
                "type" => "only-icon", 
                "class" => "openConfigService btn-primary", 
                "data" => array("serviceid" => "id"), 
                "icon" => "fa fa-wrench",
                "tooltip" => Lang::T('table','editTooltip')
            ),    
//            array(
//                "type" => "only-icon", 
//                "class" => "openReassignService btn-info", 
//                "data" => array("serviceid" => "id"), 
//                "icon" => "fa fa-exchange",
//                "tooltip" => Lang::T('table','transferInfo')
//            ),    
            array(
                "type" => "only-icon", 
                "class" => "openDeleteService btn-danger", 
                "data" => array("serviceid" => "id"), 
                "icon" => "fa fa-trash-o",
                "tooltip" => Lang::T('table','deleteTooltip')
            ),    
        );
    }
}
