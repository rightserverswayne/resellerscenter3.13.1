<?php

/* * ********************************************************************
 * MGMF product developed. (2016-02-23)
 * *
 *
 *  CREATED BY MODULESGARDEN       ->       http://modulesgarden.com
 *  CONTACT                        ->       contact@modulesgarden.com
 *
 *
 * This software is furnished under a license and may be used and copied
 * only  in  accordance  with  the  terms  of such  license and with the
 * inclusion of the above copyright notice.  This software  or any other
 * copies thereof may not be provided or otherwise made available to any
 * other person.  No title to and  ownership of the  software is  hereby
 * transferred.
 *
 *
 * ******************************************************************** */

namespace MGModule\ResellersCenter\controllers\addon\clientarea;

use MGModule\ResellersCenter\Core\Redirect;
use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;
use MGModule\ResellersCenter\repository\whmcs\Invoices as InvoicesRepo;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;
use MGModule\ResellersCenter\repository\whmcs\Orders as OrdersRepo;
use MGModule\ResellersCenter\repository\whmcs\PaymentGateways as WHMCSPaymentGateways;
use MGModule\ResellersCenter\repository\ResellersServices;
use MGModule\ResellersCenter\repository\PaymentGateways;

use MGModule\ResellersCenter\Core\Whmcs\Orders\Order;
use MGModule\ResellersCenter\Core\Whmcs\Services\Addons\Addon;
use MGModule\ResellersCenter\Core\Whmcs\Services\Domains\Domain;
use MGModule\ResellersCenter\Core\Whmcs\Services\Hosting\Hosting;
use MGModule\ResellersCenter\Core\Whmcs\Products\Domains\Registrar;
use MGModule\ResellersCenter\Core\Whmcs\Products\Upgrades\Upgrade;

use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\core\datatable\DatatableDecorator as Datatable;

use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;

use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;
use MGModule\ResellersCenter\mgLibs\exceptions\WhmcsAPI as WhmcsAPIException;

/**
 * Description of Orders
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Orders extends AbstractController
{
    const SUSPEND = 'suspend';
    const SUSPENDED_STATUS = 'Suspended';

    public function indexHTML()
    {
        $reseller = ResellerHelper::getLogged();
        
        return array(
            'tpl'   => 'base',
            'vars' => array(
                "reseller" => $reseller
            )
        );
    }
    
    public function acceptOrderJSON()
    {
        $orderid = Request::get("orderid");
        if(! $this->isBelongToReseller($orderid))
        {
            return array("error" => "Order not found");
        }
        
        try 
        {
            $reseller = ResellerHelper::getLogged();
            $order = new Order($orderid);

            //Check if order is paid - if not create invoice for the reseller
            if($order->invoice->status == InvoicesRepo::STATUS_UNPAID)
            {
                if(!$reseller->settings->admin->resellerInvoice)
                {
                    $order->createAcceptanceInvoice($reseller);
                    return array("success" => Lang::T('accept','awaiting'));
                }

                return array("error" => Lang::T('error', 'whmcsInvoiceNotPaid'));
            }

            $order->activate();
            return array("success" => Lang::T('accept','success'));
        }
        catch (WhmcsAPIException $exc) 
        {
            return array("error" => $exc->getMessage());
        }
    }
        
    public function getOrderForTableJSON()
    {
        //Only in client details view
        $clientid = Request::get("clientid");
        
        $dtRequest = Request::getDatatableRequest();
        $reseller = ResellerHelper::getLogged();
        
        $orders = new OrdersRepo();
        $result = $orders->getResellerOrdersForTable($reseller->id, $dtRequest, $clientid) ?: [];
        
        //Set Payment Method Names
        foreach($result["data"] as $row)
        {
            if($reseller->settings->admin->resellerInvoice)
            {
                $gatewaysRepo = new PaymentGateways();
                $settings = $gatewaysRepo->getGatewaySettings($reseller->id, $row->paymentmethod);
                $row->paymentmethod = $settings["displayName"];
            }
            else
            {
                $gatewaysRepo = new WHMCSPaymentGateways();
                $settings = $gatewaysRepo->getGatewaySettings($row->paymentmethod);
                $row->paymentmethod = $settings["name"];
            }
        }
        
        $format = array(
            "paymentstatus" => array(
                "lang" => array('paymentstatus'), 
                "default" => "noinvoice",
                "class" => array(array("Unpaid", "text-danger"), array("Paid", "text-success"), array("", "text-success"), array("Cancelled", "text-muted"))),
            "status" => array(
                "lang" => array('status'),
                "class" => array(array("Active", "text-success"), array("Pending", "text-danger"), array("Cancelled", "text-muted"))),
        );

        $buttons = array(
            array(
                "type" => "only-icon", 
                "class" => "openDetailsOrder btn-primary", 
                "data" => array("orderid" => "id", "paymentstatus" => "paymentstatus"), 
                "icon" => "fa fa-list", 
                "tooltip" => Lang::T('table','orderDetailsInfo')), 
            array(
                "type" => "only-icon", 
                "class" => "openAcceptOrder btn-success", 
                "data" => array("orderid" => "id", "paymentstatus" => "paymentstatus", "invoiceid" => "invoiceid"), 
                "icon" => "fa fa-check-square-o", 
                "if" => array(array("DT_RowClass", ""), array("status", "Pending")),
                "tooltip" => Lang::T('table','acceptOrderInfo')), 
        );
        
        $datatable = new Datatable($format, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }
    
    public function getOrderDetailsJSON()
    {
        $orderid = Request::get("orderid");

        $repo = new OrdersRepo();
        $order = $repo->find($orderid);
        
        $result = array();
        
        //fetch all hostings and addons from order
        foreach($order->hostings as $hosting) 
        {
            $tmp = $hosting->toArray();
            $tmp["product"] = $hosting->product->toArray();
            
            //Get Billing cycle
            $tmp["billingcycle"] = Lang::absoluteT("billingcycles", $tmp["billingcycle"]);
            $result["hostings"][] = $tmp;
        }

        //Get Addons with hosting
        foreach($order->addons as $hostingAddon) 
        {
            $addon = array_merge($hostingAddon->toArray(), array("addon" => $hostingAddon->addon->toArray()));
            $addon["billingcycle"] = Lang::absoluteT("billingcycles", $addon["billingcycle"]);
            $result["hostingAddons"][] = $addon;
        }

        //get all domains from order
        foreach($order->domains as $domain) {
            $result["domains"][] = $domain->toArray();
        }
        
        //get all domains from order
        foreach($order->upgrades as $upgrade) 
        {
            $upgrade = new Upgrade($upgrade->id);
            $result["upgrades"][] = $upgrade->getOrderDetails(); 
        }
        
        //get domain renewals
        $reseller = ResellerHelper::getLogged();
        $tiems = $reseller->settings->admin->resellerInvoice ? $order->invoice->resellerInvoice->items : $order->invoice->items;
        foreach($tiems as $item) 
        {
            if($item->type == InvoiceItems::TYPE_DOMAIN_RENEW) 
            {
                $domain = $item->domain->toArray();
                $domain["typeraw"] = "renewal";
                $domain["type"] = Lang::T("renewal");
                $result["domains"][] = $domain; 
            }
        }
        
        $result["status"] = $order->status;
        $result["invoiceid"] = $order->invoiceid;
        $result["currency"] = $order->client->currencyObj->toArray();
        return $result;        
    }

    /**
     * Get Service table
     *
     * @return array
     * @throws \Exception
     */
    public function getServicesTableJSON()
    {

        $reseller  = ResellerHelper::getLogged();
        $dtRequest = Request::getDatatableRequest();

        $result = $reseller->hosting->getForTable($dtRequest);

        $format =
        [
            "status" =>
            [
                "lang" => ['status'],
                "class" =>
                [
                    ["Active",      "text-success"],
                    ["Pending",     "text-danger"],
                    ["Suspended",   "text-muted"]
                ]
            ]
        ];

        $buttons[] =
            [
                "type"    => "only-icon",
                "class"   => "openDeleteService btn-danger",
                "data"    => ["hosting_id" => "hosting_id"],
                "icon"    => "fa fa-trash-o",
                "tooltip" => Lang::T('table', 'services', 'deleteInfo')
            ];


        if ($reseller->settings->admin->suspend == 'on' || $reseller->settings->private->suspend == 'on') {
            $buttons[] = [
                    "type"    => "only-icon",
                    "class"   => "openUnsuspendService btn-info",
                    "data"    => ["hosting_id" => "hosting_id"],
                    "icon"    => "fa fa-unlock",
                    "tooltip" => Lang::T('table', 'unsuspendService'),
                    "if"      => [["status", "==", self::SUSPENDED_STATUS]]
                ];
            $buttons[] = [
                "type"    => "only-iconn",
                "class"   => "openSuspendService btn-warning",
                "data"    => ["hosting_id" => "hosting_id"],
                "icon"    => "fa fa-lock",
                "tooltip" => Lang::T('table', 'suspendService'),
                "if"      => [["status", "!=", self::SUSPENDED_STATUS]]
            ];
        }

        $datatable = new Datatable($format, $buttons);

        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }

    public function suspendJSON()
    {
        global $CONFIG;

        $serviceId = Request::get("relid");
        $state = Request::get("state");

        $reseller = ResellerHelper::getLogged();

        if ($reseller->settings->private->suspend == 'on' || $reseller->settings->admin->suspend == 'on') {

            $resellersServices = new ResellersServices();
            $command = $state == self::SUSPEND ? 'ModuleSuspend' : 'ModuleUnsuspend';
            $postData = [
                'serviceid' => $serviceId,
                'suspendreason' => 'Abuse',
            ];

            try {
                if (!$resellersServices->serviceBelongsToReseller($serviceId, $reseller->id)) {
                    throw new \Exception(Lang::T('serviceNotBelongsToReseller'));
                }
                WhmcsAPI::request($command, $postData);;
            } catch(\Exception $e) {
                return ["error" => $e->getMessage()];
            }

            return ["success" => Lang::T($state, 'success')];
        }

        $url = parse_url($CONFIG["SystemURL"]);
        Redirect::to($url["host"], $url["path"].'/index.php', ["m" => "ResellersCenter", "mg-page" => "clients"]);
        exit;
    }

    /**
     * Get Addon table
     *
     * @return array
     * @throws \Exception
     */
    public function getAddonsTableJSON()
    {
        $reseller = ResellerHelper::getLogged();
        $dtRequest = Request::getDatatableRequest();

        $result = $reseller->addons->getForTable($dtRequest);
        $format =
        [
            "billingcycle" =>
            [
                "lang"      => ['billingcycles'],
                "absolute"  => true
            ],
            "status" =>
            [
                "lang" => ['status'],
                "class" =>
                [
                    ["Active",      "text-success"],
                    ["Pending",     "text-danger"],
                    ["Suspended",   "text-muted"]
                ]
            ],
        ];

        $buttons =
        [
            [
                "type"    => "only-icon",
                "class"   => "openDeleteAddon btn-danger",
                "data"    => ["addon_id" => "hostingaddonid"],
                "icon"    => "fa fa-trash-o",
                "tooltip" => Lang::T('table', 'addons', 'deleteInfo')
            ],
        ];

        $datatable = new Datatable($format, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }

    /**
     * Get Domains table
     *
     * @return array
     * @throws \Exception
     */
    public function getDomainsTableJSON()
    {
        $reseller   = ResellerHelper::getLogged();
        $dtRequest  = Request::getDatatableRequest();

        $result = $reseller->domains->getForTable($dtRequest);
        $format =
        [
            "status" =>
            [
                "lang" => ['status'],
                "class" =>
                [
                    ["Active",      "text-success"],
                    ["Pending",     "text-danger"],
                    ["Suspended",   "text-muted"]
                ]
            ],
            "registrar" =>
            [
                "override" => function($row)
                {
                    if(!empty($row["registrar"]))
                    {
                        $registrar  = new Registrar($row["registrar"]);
                        return  $registrar->FriendlyName ?: $registrar->name;
                    }
                }
            ]
        ];

        $buttons =
        [
            [
                "type"    => "only-icon",
                "class"   => "openDeleteDomain btn-danger",
                "data"    => ["domain_id" => "domain_id"],
                "icon"    => "fa fa-trash-o",
                "tooltip" => Lang::T('table', 'domains', 'deleteInfo')
            ],
        ];

        $datatable = new Datatable($format, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }

    /**
     * Get available filter values
     *
     * @return mixed
     * @throws \Exception
     */
    public function getFiltersDataJSON()
    {
        $type     = Request::get("type");
        $search   = Request::get("term");
        $reseller = ResellerHelper::getLogged();

        $filter = $reseller->view->datatable->filters->get($type);
        $result = $filter->getData($search);

        return $result;
    }

    /**
     * Terminate/Cancel service
     *
     * @return array
     * @throws \Exception
     */
    public function terminateServiceJSON()
    {
        $reseller   = ResellerHelper::getLogged();
        $relid      = Request::get("relid");
        $type       = Request::get("type");

        try
        {
            switch($type)
            {
                case ResellersServices::TYPE_HOSTING:
                    $model = $reseller->hosting->find($relid);

                    $hosting = new Hosting($model);
                    $hosting->terminate();
                    break;
                case ResellersServices::TYPE_ADDON:
                    $model = $reseller->addons->find($relid);

                    $addon = new Addon($model);
                    $addon->terminate();
                    break;
                case ResellersServices::TYPE_DOMAIN:
                    $model = $reseller->domains->find($relid);

                    $domain = new Domain($model);
                    $domain->cancel();
                    break;
                default:
                    throw new \Exception("Invalid service type provided");
            }
        }
        catch (\Exception $ex)
        {
            return array("error" => $ex->getMessage());
        }

        return array("success" => Lang::T('terminate', 'success'));
    }
    
    private function isBelongToReseller($orderid)
    {
        $reseller = ResellerHelper::getLogged();
        
        $repo = new OrdersRepo();
        $order = $repo->find($orderid);
        if($reseller->id == $order->clientRC->reseller_id)
        {
            return true;
        }
        
        return false;
    }
}

      /**
//     * Not used in version 3.0.0
//     * 
//     * @return type
//     */
//    public function deleteOrderJSON()
//    {
//        $orderid = Request::get("orderid");
//        if(! $this->isBelongToReseller($orderid)) {
//            return array("error" => "Order not found");
//        }
//        
//        try 
//        {
//            WhmcsAPI::request("deleteorder", array("orderid" => $orderid));
//            EventManager::call("orderDeleted", $orderid);
//        } 
//        catch (WhmcsAPIException $exc) 
//        {
//            return array("error" => $exc->getMessage());
//        }
//        
//        return array("success" => Lang::T('delete','success'));
//    }
//    
//    /**
//     * Not used in version 3.0.0
//     * 
//     * @return type
//     */
//    public function cancelOrderJSON()
//    {
//        $orderid = Request::get("orderid");
//        if(! $this->isBelongToReseller($orderid)) {
//            return array("error" => "Order not found");
//        }
//        
//        try 
//        {
//            WhmcsAPI::request("cancelorder", array("orderid" => $orderid));
//            EventManager::call("orderCancelled", $orderid);
//        }
//        catch (WhmcsAPIException $exc) 
//        {
//            return array("error" => $exc->getMessage());
//        }
//        
//        return array("success" => Lang::T('cancel','success'));
//    }
//    
//    /**
//     * Not used in version 3.0.0
//     * 
//     * @return type
//     */
//    public function fraudOrderJSON()
//    {
//        $orderid = Request::get("orderid");
//        if(! $this->isBelongToReseller($orderid)) {
//            return array("error" => "Order not found");
//        }
//        
//        try 
//        {
//            WhmcsAPI::request("fraudorder", array("orderid" => $orderid));
//            EventManager::call("orderMarkAsFraud", $orderid);
//        }
//        catch (WhmcsAPIException $exc) 
//        {
//            return array("error" => $exc->getMessage());
//        }
//        
//        return array("success" => Lang::T('fraud','success'));
//    }