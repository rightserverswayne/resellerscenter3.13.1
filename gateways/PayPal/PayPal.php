<?php
namespace MGModule\ResellersCenter\gateways\PayPal;
use MGModule\ResellersCenter\core\Redirect;
use MGModule\ResellersCenter\core\resources\gateways\PaymentGateway;
use MGModule\ResellersCenter\core\resources\gateways\interfaces\InvoiceGateway;

use MGModule\ResellersCenter\repository\whmcs\Currencies;

use MGModule\ResellersCenter\core\form\fields\Switcher;
use MGModule\ResellersCenter\core\form\fields\Select;
use MGModule\ResellersCenter\core\form\fields\Text;
use MGModule\ResellersCenter\core\form\Form;

use MGModule\ResellersCenter\mgLibs\Smarty;
use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice;
use MGModule\ResellersCenter\core\Server;

/**
 * Description of PayPal
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class PayPal extends PaymentGateway implements InvoiceGateway
{    
    public $adminName = "PayPal";
    
    public $type = "Invoices";

    protected static string $sysName = 'paypal';
    
    //Set configuration form
    public function __construct() 
    {
        $status = new Switcher("enabled", "Status");
        $status->addStyle("width", 9);
        
        $displayName = new Text("displayName", "Display Name", "Name that will be displayed on order form", "PayPal");
        $displayName->addStyle("width", 9);
        
        $email = new Text("email", "PayPal Email");
        $email->addStyle("width", 9);
        
        $apiUsername = new Text("username", "API Username");
        $apiUsername->addStyle("width", 9);
        
        $apiPassword = new Text("password", "API Password");
        $apiPassword->addStyle("width", 9);
        
        $apiSignature = new Text("signature", "API Signature", "API Details are required for Refunds and Subscription cancellations");
        $apiSignature->addStyle("width", 9);
        
        $options = $this->getCurrenciesOptions();
        $convertto = new Select("convertto", "Convert To For Processing", "", 0, "", $options);
        $convertto->addStyle("width", 9);
        
        $sandbox = new Switcher("sandbox", "Sandbox Mode", "Use PayPal’s Virtual Sandbox Test Environment - requires a separate Sandbox Test Account");
        $sandbox->addStyle("width", 9);

        $this->configuration = new Form();
        $this->configuration->add($status);
        $this->configuration->add($displayName);
        $this->configuration->add($email);
        $this->configuration->add($apiUsername);
        $this->configuration->add($apiPassword);
        $this->configuration->add($apiSignature);
        $this->configuration->add($convertto);
        $this->configuration->add($sandbox);
        
        parent::__construct();
    }
    
    public function link(\MGModule\ResellersCenter\Core\Resources\Invoices\Invoice $invoice)
    {
        global $whmcs;
        
        $params["invoice"] = $invoice;
        $params["paypalemail"] = $this->email;

        $invoicenum = $invoice->invoicenum ?: $invoice->id;
        $params["description"] = "{$invoice->reseller->settings->private->companyName} - {$whmcs->get_lang("invoicenumber")}{$invoicenum}";
        
        $params["amount"] = $invoice->total - $invoice->amountpaid;
        $params["currency"] = $invoice->client->currencyObj->code;
        if($this->convertto)
        {
            $params["amount"] = convertCurrency($params["amount"], $invoice->client->currency, $this->convertto);
            
            $repo = new Currencies();
            $currency = $repo->find($this->convertto);
            $params["currency"] = $currency->code;
        }

        $params["clientdetails"] = $invoice->client;
        $params["phone1"] = substr($invoice->client->phonenumber, 0, 3);
        $params["phone2"] = substr($invoice->client->phonenumber, 3, 6);
        $params["phone3"] = substr($invoice->client->phonenumber, 6, 9);
                
        $recurring = $this->getRecurringBillingValues($invoice);
        $params["subpossible"] = empty($recurring) ? false : true;
        
        $params["charset"] = $whmcs->get_config("Charset");
        
        $params["returnSuccess"] = Server::getCurrentURL(array("id" => $invoice->id, "paymentsuccess" => "true"));
        $params["returnCancel"] = Server::getCurrentURL(array("id" => $invoice->id, "paymentfailed" => "true"));
        
        $params["notifyUrl"] = Server::getCurrentSystemURL() . "modules/addons/ResellersCenter/gateways/callback/PayPal.php";
        $params["paypalUrl"] = ($this->sandbox) ? "https://www.sandbox.paypal.com/cgi-bin/webscr" : "https://www.paypal.com/cgi-bin/webscr";

        $params = empty($recurring) ? $params : array_merge($params, $recurring);
        $html = Smarty::I()->view("PayPalBtn", $params, __DIR__);
        return $html;
    }
        
    public function callback($data)
    {
        $description = "";
        $query = array("cmd" => "_notify-validate");
        foreach($data as $key => $value) 
        {
            $description = "{$key} => {$value}, \r\n";
            $query[$key] = \WHMCS\Input\Sanitize::decode($value);
        }
        
        $paypalUrl = ($this->sandbox) ? "https://ipnpb.sandbox.paypal.com/us/cgi-bin/webscr" : "https://ipnpb.paypal.com/cgi-bin/webscr";
        $options = array("User-Agent" => "PHP-IPN-VerificationScript");
        $ipn = curlCall($paypalUrl, http_build_query($query), $options);

        if(strcmp($ipn, "VERIFIED") != 0)
        {
            if(strcmp($ipn, "INVALID") == 0)
            {
                $this->log("IPN Handshake Invalid");
                header("HTTP/1.0 406 Not Acceptable");
                exit();
            }
            else
            {
                $this->log("IPN Handshake Error", $description . "\r\n\r\nIPN Handshake Response => " . $ipn);
                header("HTTP/1.0 406 Not Acceptable");
                exit();
            }
        }
        
        if(strcmp($data["payment_status"], "Pending") == 0) 
        {
            $this->log("Pending");
            exit();
        }
        
        //Update Transaction if payment is completed
        $repo = new \MGModule\ResellersCenter\repository\Transactions();
        $transaction = $repo->getByTransId($data['txn_id']);
        if($data['txn_type'] == 'web_accept' && $data['invoice'] && $data['payment_status'] == 'Completed' )
        {
            $transaction->fees = $data['mc_fee'];
            $transaction->save();
            exit();
        }
        
        //Skip transaction creation if already exists
        if(isset($data['txn_id']) && isset($transaction))
        {
            exit();
        }
        
        $currenciesRepo = new \MGModule\ResellersCenter\repository\whmcs\Currencies();
        $ppCurrency = $currenciesRepo->getByCode($data["mc_currency"]);
        
        if(empty($ppCurrency)) 
        {
            $this->log("Unrecognised Currency");
        }
        
        //Currently subscriptions are not supported
        if($data['txn_type'] != 'web_accept')
        {
            exit();
        }
        
        if($data['payment_status'] != "Completed") 
        {
            $this->log("Incomplete");
            exit();
        }
        
        //Add payment to invoice
        try
        {
            $this->log("Successful");
            $invoice = new Invoice($data["custom"]);
            
            if($ppCurrency->id != $invoice->client->currency)
            {
                $data['mc_gross'] = convertCurrency($data['mc_gross'] , $ppCurrency->id, $invoice->client->currency);
                $data['mc_fee'] = convertCurrency($data['mc_fee'] , $ppCurrency->id, $invoice->client->currency);

                if($invoice->total < $data['mc_gross'] + 1 && $data['mc_gross'] - 1 < $invoice->total) 
                {
                    $data['mc_gross'] = $invoice->total;
                }
            }
            $invoice->payments->addTransaction(0, $data["txn_id"], $data["mc_gross"], $data["mc_fee"], "PayPal");
        }
        catch(\Exception $ex)
        {
            exit();
        }
    }
    
    public function refund($transid)
    {
        $url = ($this->sandbox) ? "https://www.sandbox.paypal.com/us/cgi-bin/webscr" : "https://www.paypal.com/cgi-bin/webscr";
        
        $repo = new \MGModule\ResellersCenter\repository\Transactions();
        $transaction = $repo->find($transid);
        
        $currencyRepo = new \MGModule\ResellersCenter\repository\whmcs\Currencies();
        $currency = empty($transaction->currency) ? $currencyRepo->find($transaction->currency) : $currencyRepo->getDefault();
        
        $postfields = array();
	$postfields["VERSION"] = "3.0";
	$postfields["METHOD"] = "RefundTransaction";
	$postfields["BUTTONSOURCE"] = "WHMCS_WPP_DP";
	$postfields["USER"] = $this->username;
	$postfields["PWD"] = $this->passwords;
	$postfields["SIGNATURE"] = $this->signature;
	$postfields["TRANSACTIONID"] = $transaction->transid;
	$postfields["REFUNDTYPE"] = "Partial";
	$postfields["AMT"] = $transaction->amountin;
	$postfields["CURRENCYCODE"] = $currency->code;

        $resultString = curlCall($url, $postfields);
        $result = explode("&", $resultString);
        
        $return = array();
	foreach($result as $line) 
        {
            $line = explode("=", $line);
            $return[$line[0]] = urldecode($line[1]);
	}

	if(strtoupper($return["ACK"]) == "SUCCESS") 
        {
            return array("status" => "success", "rawdata" => $return, "transid" => $return["REFUNDTRANSACTIONID"], "fees" => $return["FEEREFUNDAMT"]);
	}

	return array("status" => "error", "rawdata" => $return);
    }
    
    private function getRecurringBillingValues(\MGModule\ResellersCenter\Core\Resources\Invoices\Invoice $invoice)
    {
        global $CONFIG;
        $billingcycles = array("monthly" => 1, "quarterly" => 3, "semiannually" => 6, "annually" => 12, "biennially" => 24, "triennially" => 36);
        
        if(!function_exists("getBillingCycleMonths")) 
        {
            require \MGModule\ResellersCenter\Addon::getWHMCSDIR() . "includes" . DS . "gatewayfunctions.php";
        }
        
        //Get First service
        $service = null;
        foreach($invoice->items as $item) 
        {
            if($item->service instanceof \MGModule\ResellersCenter\models\whmcs\Hosting)
            {
                $service = $item->service;
                break;
            }
        }
        
        if($service)
        {
            if($service->billingcycle == "onetime" || $service->billingcycle == 'freeacount') {
                return array();
            }
            
            $recurringcycleperiod = $billingcycles[$service->billingcycle];
            $recurringcycleunits = "Months";

            if($recurringcycleperiod >= 12)
            {
                $recurringcycleperiod = $recurringcycleperiod / 12;
                $recurringcycleunits = "Years";
            }
            
            $params["primaryserviceid"] = $service->id;
            $params["firstpaymentamount"] = $invoice->amountpaid;
            
            $recurringamount = 0;
            foreach($invoice->items as $item)
            {
                if($item->service instanceof \MGModule\ResellersCenter\models\whmcs\Hosting || $item->service instanceof \MGModule\ResellersCenter\models\whmcs\HostingAddon)
                {
                    $recurringamount += ($invoice->service->taxed ? $this->calcTax($invoice->service->amount, $invoice->taxrate, $invoice->taxrate2) : $invoice->service->amount);
                }
                
                if($item->service instanceof \MGModule\ResellersCenter\models\whmcs\Domain)
                {
                    $recurringamount += ($CONFIG["TaxDomain"] ? $this->calcTax($item->service->recurringamount, $invoice->taxrate, $invoice->taxrate2) : $item->service->recurringamount);
                }
            }
            
            $params['recurringamount'] = $recurringamount;
            $params["recurringcycleperiod"] = $recurringcycleperiod;
            $params["recurringcycleunits"] = $recurringcycleunits;
            $params["overdue"] = ($invoice->duedate < date("Ymd")) ? true : false;
        }
        
        return $params;
    }

    private function calcTax($amount, $taxrate, $taxrate2)
    {
        global $CONFIG;
        
        if($CONFIG['TaxType'] == "Exclusive") 
        {
            if ($CONFIG['TaxL2Compound'])
            {
                $amount = $amount + $amount * ($taxrate / 100);
                $amount = $amount + $amount * ($taxrate2 / 100);
            }
            else 
            {
                $amount = $amount + $amount * ($taxrate / 100) + $amount * ($taxrate2 / 100);
            }
        }
        
        return $amount;
    }
}
