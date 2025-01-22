<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\libs\CreditLine\Services\CreditLineService;
use MGModule\ResellersCenter\repository\ResellersProfits;

use MGModule\ResellersCenter\repository\whmcs\Orders;
use MGModule\ResellersCenter\repository\whmcs\Invoices;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;

use MGModule\ResellersCenter\Core\Whmcs\Invoices\Invoice as WhmcsInvoice;
use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;
use MGModule\ResellersCenter\core\EventManager;

/**
 * Description of InvoicePaid
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class InvoicePaid 
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     * 
     * @var array 
     */
    public $functions;
    
    public static $params;
    
    /**
     * Assign anonymous function
     */
    public function __construct() 
    {
        $this->functions[-999999] = function($params) {
            self::$params = $params;
            $this->restoreItemsTypes(self::$params);
        };

        $this->functions[10] = function($params) {
            self::$params = $params;
            return $this->activateOrder(self::$params);
        };

        $this->functions[20] = function($params) {
            self::$params = $params;
            return $this->makeRefundForReseller(self::$params);
        };

        $this->functions[30] = function($params) {
            self::$params = $params;
            return $this->giveBackCredit(self::$params);
        };

        $this->functions[PHP_INT_MAX] = function($params) {
            self::$params = $params;
            return $this->addResellerProfit(self::$params);
        };
    }
    
    /**
     * Activate order that reseller paid for.
     * This is used when reseller is paying for his client order.
     * 
     * @param type $params
     * @return type
     */
    public function activateOrder($params)
    {
        // To nie ma prawa działać $item->relid  to  id  hosta a nie orderu !
        $orders = new Orders();
        $invoices = new Invoices();
        $invoice = $invoices->find($params["invoiceid"]);

        foreach($invoice->items as $item)
        {
            if($item->type == InvoiceItems::TYPE_RC_ORDER) 
            {
                $order = $orders->find($item->relid);
                if($order->status == Orders::STATUS_PENDING)
                {
                    WhmcsAPI::request("acceptorder", array("orderid" => $item->relid));
                    break;
                }
            }
        }
        
        return $params;
    }
    
    /**
     * If reseller paid for client then after client payment he
     * should get refund for that
     * 
     * @param type $params
     */
    public function makeRefundForReseller($params)
    {
        $invoices = new Invoices();
        $invoice = $invoices->find($params["invoiceid"]);
        
        //Check if reseller paid for client's order
        $invoiceItems = new InvoiceItems();
        $item = $invoiceItems->getItemByRelidAndType($invoice->order->id, InvoiceItems::TYPE_RC_ORDER);
        if(!empty($item))
        {
            $result = WhmcsAPI::request("AddCredit", array(
                "clientid" => $item->userid, 
                "amount" => $item->amount,
                "description" => "ResellersCenter Refund for Invoice #{$item->invoice->id}. Invoice has been paid by client."
            ));
        }

        return $params;
    }

    public function giveBackCredit($params)
    {
        $invoices = new Invoices();
        $invoice = $invoices->find($params["invoiceid"]);
        if ($invoice->exists) {
            $creditLineService = new CreditLineService();
            $creditLineService->addPayment($invoice);
        }
        return $params;
    }

    public function restoreItemsTypes($params)
    {
        $invoices = new Invoices();
        $invoice = $invoices->find($params["invoiceid"]);
        $pattern = "/".InvoiceItems::TYPE_COMPLETED_PREFIX . "(.+)" .InvoiceItems::TYPE_COMPLETED_SUFFIX . "/";

        foreach ($invoice->items as $item) {
            $matches = [];
            if (preg_match($pattern, $item->type, $matches)) {
                $item->type = $matches[1];
                $item->save();
            }
        }
        return $params;
    }
    
    /**
     * Add transaction relation to reseller
     * 
     * @param type $params
     * @return type
     */
    public function addResellerProfit($params)
    {
        $invoice = new WhmcsInvoice($params["invoiceid"]);
        
        //Check if invoice is related with reseller
        $reseller = $invoice->getReseller();

        if(!$reseller->exists || $reseller->settings->admin->resellerInvoice)
        {
            return;
        }
        
        $profits = $invoice->getProfits();
        foreach($profits as $profit)
        {
            $amount = convertCurrency($profit["amount"], $invoice->client->currency, $reseller->client->currency);
            if($amount > 0)
            {
                $repo = new ResellersProfits();
                $repo->createNew($reseller->id, $profit["itemid"], $profit["itemrelid"], $amount);
                
                EventManager::call("profitAdded", $profit["amount"], $amount, $profit["relid"], $reseller->id);
            }
            elseif($amount < 0)
            {
                $reseller->client->credits->add($amount, "ResellersCenter Payment for Invoice ID: {$params["invoiceid"]}");
            }
        }
        
        return $params;
    }
}