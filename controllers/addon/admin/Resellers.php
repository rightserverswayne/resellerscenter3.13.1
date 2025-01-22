<?php

namespace MGModule\ResellersCenter\Controllers\Addon\Admin;

use MGModule\ResellersCenter\mgLibs\process\AbstractController;

use MGModule\ResellersCenter\repository\CreditLines;
use MGModule\ResellersCenter\repository\Invoices as RcInvoicesRepo;
use MGModule\ResellersCenter\repository\Whmcs\Invoices as WhmcsInvoicesRepo;
use MGModule\ResellersCenter\repository\ResellersPricing;
use MGModule\ResellersCenter\repository\Resellers as ResellersRepo;
use MGModule\ResellersCenter\repository\Groups;
use MGModule\ResellersCenter\repository\Documentations;

use MGModule\ResellersCenter\repository\ResellersSettings;
use MGModule\ResellersCenter\repository\whmcs\Currencies;
use MGModule\ResellersCenter\repository\whmcs\EmailTemplates;
use MGModule\ResellersCenter\repository\whmcs\PaymentGateways;
use MGModule\ResellersCenter\repository\whmcs\TicketDepartments;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\core\helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\core\datatable\DatatableDecorator as Datatable;
use MGModule\ResellersCenter\mgLibs\Lang;

use MGModule\ResellersCenter\core\Request;
/**
 * Description of Resellers
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Resellers extends AbstractController
{
    public function indexHTML()
    {
        $vars = [];
                
        $groups = new Groups();
        $vars["groups"] = $groups->all();
        
        return ['tpl'   => 'base', 'vars' => $vars];
    }
    
    public function getResellersJSON()
    {
        $resellers = new ResellersRepo();
        $result = $resellers->all();
        
        return $result->toArray();
    }

    public function createResellerJSON()
    {
        $clientid = Request::get("clientid");
        $groupid  = Request::get("groupid");

        //Check if selected client is not already a reseller
        $repo = new ResellersRepo();
        $resellers = $repo->getResellerByClientId($clientid);
        if (! empty($resellers)) {
            return array("error" => Lang::T('add','error','alreadyexists'));
        }

        //Create reseller
        $model = $repo->createNew($clientid, $groupid);
        EventManager::call("resellerCreated", $clientid, $groupid);

        //Generate default pricing
        if (Request::get("generateDefaultProducts")) {
            $reseller = new Reseller($model);
            $reseller->contents->generateDefaultPricing();
        }
        $defaultSettings = (new ResellersSettings())->getSettings(0);

        $onlyAdminSettings = ['showHidden'];

        if (!empty($defaultSettings)) {
            $settingsToSave = array_diff($defaultSettings,$onlyAdminSettings);
            (new ResellersSettings())->saveSettings($model->id, $settingsToSave);
        }

        return ["success" => Lang::T('add','success')];
    }
    
    public function deleteResellerJSON()
    {
        $resellerid = Request::get("resellerid");
        
        $resellers = new ResellersRepo();
        $resellers->deleteWithRelations($resellerid);

        EventManager::call("resellerDeleted", $resellerid);
        return array("success" => Lang::T('delete','success'));
    }
    
    public function updateResellerGroupJSON()
    {
        $groupid    = Request::get("groupid");
        $resellerid = Request::get("resellerid");
        
        $reseller = new ResellersRepo();
        $reseller->updateGroup($resellerid, $groupid);
        
        //Reset old reseller pricing
        $pricingRepo = new ResellersPricing();
        $pricingRepo->deletePricing($resellerid);
        
        EventManager::call("resellerReassign", $resellerid, $groupid);
        return array("success" => Lang::T('group','update','success'));
    }
    
    public function getResellersForDataTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();

        $currencies = new Currencies();
        $currency = $currencies->getDefault();
        
        $resellers = new ResellersRepo();
        $result = $resellers->getDataForTable($dtRequest);

        $format = [
            "groupname" => ["link" => ["group_id", "group"]],
            "firstname" => ["link" => ["client_id", "client"]],
            "lastname"  => ["link" => ["client_id", "client"]],
            "status"    => ["lang" => ['status'], "default" => "off"],
                "class" => [["on", "label label-success"], ["off", "label label-default"]],
            "totalsale" => ["prefix" => $currency->prefix, "suffix" => $currency->suffix],
            "monthsale" => ["prefix" => $currency->prefix, "suffix" => $currency->suffix]
        ];
        
        $buttons = array(
            array(
                "type" => "only-icon", 
                "class" => "openDetailsReseller btn-primary", 
                "data" => array("resellerid" => "id"), 
                "icon" => "fa fa-pencil-square-o ",
                "tooltip" => Lang::T('table','detailsTooltip')),    
            array(
                "type" => "only-icon", 
                "class" => "openDeleteReseller btn-danger", 
                "data" => array("resellerid" => "id"), 
                "icon" => "fa fa-trash-o", 
                "tooltip" => Lang::T('table','deleteTooltip')),    
        );
        
        $datatable = new Datatable($format, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }
    
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Details
     */
    public function detailsHTML()
    {
        $resellerid = Request::get("rid");
        $reseller = new Reseller($resellerid);

        $vars = [];
        $groups = new Groups();
        $vars["groups"] = $groups->all();
        $resellerRepo = new ResellersRepo();
        $resellerModel = $resellerRepo->find($resellerid);
        $resellerModel->hasRelatedInvoices = ResellerHelper::hasResellerRelatedInvoices($reseller);
        $vars["reseller"] = $resellerModel;
        $vars["totalsale"] = number_format($resellerRepo->getResellerSale($resellerid), 2);
        $vars["monthlysale"] = number_format($resellerRepo->getResellerSale($resellerid, date("Y-m-1", strtotime("-1 month")), date("Y-m-t", strtotime("-1 month"))), 2);
    
        $currencies = new Currencies();
        $vars["currency"] = $currencies->getDefault();

        $vars["settings"] = $reseller->settings->admin;
        $creditLineRepo = new CreditLines();
        $creditLine = $creditLineRepo->getByClientId($reseller->client_id);

        if ($creditLine && $creditLine->limit) {
            $vars["creditline"] = $creditLine;
        }

        $vars["privateSettings"] = $reseller->settings->private;
        
        $ticketDepartments = new TicketDepartments();
        $vars["ticketDepts"] = $ticketDepartments->all();
        $emailTemplates = new EmailTemplates();
        $vars["emailTemplates"] = $emailTemplates->getTemplatesSortedByType();
        $gateways = new PaymentGateways();
        $vars["gateways"] = $gateways->getEnabledGatewaysArray();
        
        $vars["whmcsTemplates"] = Whmcs::getAvailableTemplates();
        $vars["orderTemplates"] = Whmcs::getAvailableOrderTemplates();
        $vars["invoiceTemplates"] = Helper::getAvailableInvoiceTemplates();

        $whmcsInvoices = new WhmcsInvoicesRepo();
        if ($reseller->settings->admin->resellerInvoice) {
            $rcInvoices = new RcInvoicesRepo();
            $rcInvoices->getUnpaidInvoicesCount($resellerid);
            $vars["resellerHasUnpaidInvoices"] = $whmcsInvoices->getUserUnpaidInvoicesCount($reseller->client->id) > 0;
            $vars["endClientHasUnpaidInvoices"] = $rcInvoices->getUnpaidInvoicesCount($resellerid) > 0;
        } else {
            $vars["resellerHasUnpaidInvoices"] = true;
            $vars["endClientHasUnpaidInvoices"] = $whmcsInvoices->getUnpaidRelatedInvoicesCount($resellerid) > 0;
        }

        $docsRepo = new Documentations();
        $vars["documentations"] = $docsRepo->all();

        $vars["isWhmcs8"] = Whmcs::isVersion('8.0');
        
        return array(
            'tpl'   => 'details/base',
            'vars' => $vars
        );
    }
}
