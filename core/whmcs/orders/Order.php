<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Orders;

use MGModule\ResellersCenter\Core\EventManager;
use MGModule\ResellersCenter\Core\Helpers\Files;
use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Core\Whmcs\Invoices\Invoice;
use MGModule\ResellersCenter\Core\Whmcs\Services\Addons\Addon;
use MGModule\ResellersCenter\Core\Whmcs\WhmcsObject;

use MGModule\ResellersCenter\repository\whmcs\HostingAddons;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;
use MGModule\ResellersCenter\repository\whmcs\Invoices;

use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;
use MGModule\ResellersCenter\mgLibs\Lang;


/**
 * Description of Order.php
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Order extends WhmcsObject
{
    /**
     * Get model class
     *
     * @return string
     */
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\models\whmcs\Order::class;
    }

    /**
     * Get Reated reseller
     *
     * @return \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller|null
     */
    public function getReseller()
    {
        $invoice = new Invoice($this->invoice);
        return $invoice->getReseller();
    }

    /**
     * Activate order
     *
     * @throws \MGModule\ResellersCenter\mgLibs\exceptions\WhmcsAPI
     */
    public function activate()
    {
        WhmcsAPI::request("AcceptOrder", array("orderid" => $this->id));

        //Activate addons - WHMCS is skipping addons for some reason...
        foreach($this->invoice->items as $item)
        {
            if($item->service instanceof Addon && $item->service->status == HostingAddons::STATUS_PENDING)
            {
                WhmcsAPI::request("UpdateClientAddon", array(
                    "id" => $item->service->id,
                    "status" => HostingAddons::STATUS_ACTIVE
                ));
            }
        }

        EventManager::call("orderAccepted", $this->id);
    }

    /**
     * Create invoice for reseller
     */
    public function createAcceptanceInvoice(Reseller $reseller)
    {
        if(!function_exists("updateInvoiceTotal"))
        {
            require_once Files::getWhmcsPath("includes", "invoicefunctions.php");
        }

        global $whmcs;
        $gateway = Whmcs::getFirstAvailableGateway();
        $paymentmethod  = $reseller->client->paymentmethod ?: $gateway["sysname"];

        $amount = Helper::calcCurrencyValue($this->invoice->total, $this->invoice->client->currency, $reseller->client->currency);
        
        $dueDateDays    = $whmcs->get_config("OrderDaysGrace");
        $dueDateStrPart = $dueDateDays >= 0 ? '+ '.$dueDateDays : '- '.abs($dueDateDays);

        $params['userid'] = $reseller->client->id;
        $params['date'] = date("Y-m-d");
        $params['duedate'] = date("Y-m-d", strtotime("{$dueDateStrPart} Days"));
        $params['status'] = Invoices::STATUS_UNPAID;
        $params['paymentmethod'] = $paymentmethod;

        $invoice = new Invoice();
        $invoice->create($params);

        $invoice->addItem($this->id, InvoiceItems::TYPE_RC_ORDER, Lang::T('invoice','resellerpayment') . " #{$this->invoice->id} - {$this->client->firstname} {$this->client->lastname}", $amount, 0);
        updateInvoiceTotal($invoice->id);

        EventManager::call("resellerInvoiceCreated", $this->id, $invoice->id);
    }

}