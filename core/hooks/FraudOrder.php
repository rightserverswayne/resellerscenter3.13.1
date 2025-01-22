<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\repository\whmcs\Invoices;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;
use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;

use MGModule\ResellersCenter\core\EventManager;
/**
 * Description of FraudOrder
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class FraudOrder 
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
            EventManager::call("orderFraudFailed", $ex->getMessage());
        }
    }
}