<?php
namespace MGModule\ResellersCenter\gateways\BankTransfer;

use MGModule\ResellersCenter\core\resources\gateways\PaymentGateway;
use MGModule\ResellersCenter\core\resources\gateways\interfaces\InvoiceGateway;

use MGModule\ResellersCenter\core\form\fields\Switcher;
use MGModule\ResellersCenter\core\form\fields\Textarea;
use MGModule\ResellersCenter\core\form\fields\Text;
use MGModule\ResellersCenter\core\form\fields\Select;
use MGModule\ResellersCenter\core\form\Form;

use MGModule\ResellersCenter\mgLibs\Smarty;

/**
 * Description of BankTransfer
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class BankTransfer extends PaymentGateway implements InvoiceGateway
{
    public $adminName = "Bank Transfer";
    
    public $type = "Invoices";

    protected static string $sysName = 'banktransfer';
    
    //Set configuration form
    public function __construct() 
    {
        $status = new Switcher("enabled", "Status");
        $status->addStyle("width", 9);
        
        $displayName = new Text("displayName", "Display Name", "Name that will be displayed on order form", "Bank Transfer");
        $displayName->addStyle("width", 9);
        
        $instructions = new Textarea("instructions", "Instructions", "bankTransferInstructions", "Bank Name:\nPayee Name:\nSort Code:\nAccount Number:");
        $instructions->addStyle("width", 9);
        $instructions->addStyle("custom", array("height" => "125px"));
        
        $options = $this->getCurrenciesOptions();
        $convertto = new Select("convertto", "Convert To For Processing", "", 0, "", $options);
        $convertto->addStyle("width", 9);
        
        $this->configuration = new Form();
        $this->configuration->add($status);
        $this->configuration->add($displayName);
        $this->configuration->add($instructions);
        $this->configuration->add($convertto);
        
        parent::__construct();
    }
    
    /**
     * Get html code for banktransfer information
     * 
     * @param \MGModule\ResellersCenter\Core\Resources\Invoices\Invoice $invoice
     * @return type
     */
    public function link(\MGModule\ResellersCenter\Core\Resources\Invoices\Invoice $invoice)
    {
        global $whmcs;
        
        $invoicenum = ($invoice->invoicenum == "") ? $invoice->id : $invoice->invoicenum;
        
        $params["instruction"] = nl2br($this->instructions);
        $params["refrencenumber"] = $whmcs->get_lang("invoicerefnum").": {$invoicenum}"; 
        
        $html = Smarty::I()->view("BankTransferInfo", $params, __DIR__);
        return $html;
    }
        
    public function callback($data)
    {
        //Not used in this gateway
    }
    
    public function refund($transid)
    {
        //Not used in this gateway
    }
}
