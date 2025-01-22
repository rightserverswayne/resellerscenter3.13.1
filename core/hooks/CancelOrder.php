<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\libs\CreditLine\Services\CreditLineService;
use MGModule\ResellersCenter\repository\whmcs\Invoices;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;
use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;

use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\repository\whmcs\Orders;

/**
 * Description of CancelOrder
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class CancelOrder 
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
        $this->functions[10] = function($params) {
            return $this->cancelResellerInvoice($params);
        };

        $this->functions[20] = function($params) {
            return $this->giveBackCreditLimit($params);
        };
    }
    
    /**
     * Cancel invoice created for reseller
     * 
     * @param type $params
     * @return type
     */
    public function cancelResellerInvoice($params)
    {
        try
        {
            $repo = new InvoiceItems();
            $item = $repo->getItemByRelidAndType($params["orderid"], InvoiceItems::TYPE_RC_ORDER);
            
            if(!empty($item->invoice))
            {
                WhmcsAPI::request("updateinvoice", array(
                    "invoiceid" => $item->invoice->id,
                    "status" => Invoices::STATUS_CANCELLED,
                ));
            }
        }
        catch(\Exception $ex) 
        {
            die($ex->getMessage());
            EventManager::call("orderFraudFailed", $ex->getMessage());
        }
    }

    public function giveBackCreditLimit($params)
    {
        $ordersRepo = new Orders();
        $order = $ordersRepo->find($params["orderid"]);
        $invoices = new Invoices();
        $invoice = $invoices->find($order->invoiceid);
        $creditLineService = new CreditLineService();
        if ($invoice->exists) {
            $creditLineService->addPayment($invoice);
            $reseller = Reseller::getResellerObjectByHisClientId($order->userid);
            if ($reseller->settings->admin->resellerInvoice) {
                $invoicesRepo = new \MGModule\ResellersCenter\repository\Invoices();
                $rcInvoice = $invoicesRepo->getByRelationInvoiceId($invoice->id);
                if ($rcInvoice->exists) {
                    $creditLineService->addPayment($rcInvoice);
                }
            }
        }

        return $params;
    }
}