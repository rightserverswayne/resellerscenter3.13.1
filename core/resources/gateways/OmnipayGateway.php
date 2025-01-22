<?php
namespace MGModule\ResellersCenter\core\resources\gateways;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\core\Server;

use MGModule\ResellersCenter\core\form\Form;
use MGModule\ResellersCenter\core\form\fields\Text;
use MGModule\ResellersCenter\core\form\fields\Select;
use MGModule\ResellersCenter\core\form\fields\Switcher;

use MGModule\ResellersCenter\mgLibs\Smarty;
use \Omnipay\Omnipay;

/**
 * Description of OmnipayGateway
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
abstract class OmnipayGateway extends PaymentGateway
{
    /**
     * Omnipay gateway object
     * 
     * @var \Omnipay\Common\AbstractGateway
     */
    protected $gateway;
    
    /**
     * Load configuration
     */
    public function __construct() 
    {
        $this->name = (new \ReflectionClass($this))->getShortName();
        
        $factory = Omnipay::getFactory();
        $this->gateway = $factory->create($this->name);
        
        $this->loadForm();
        parent::__construct();
    }
    
    public function link(\MGModule\ResellersCenter\Core\Resources\Invoices\Invoice $invoice)
    {
        $params["invoiceid"] = $invoice->id;
        $params["systemurl"] = Server::getCurrentSystemURL();
        $params["langpaynow"] = Whmcs::lang("invoicespaynow");
        
        $dir = ROOTDIR.DS."modules".DS."addons".DS."ResellersCenter".DS."gateways".DS.$this->name;
        return Smarty::I()->view("{$this->name}Btn", $params, $dir);
    }
    
    /**
     * Capture credit card payment
     * 
     * @param type $params from rccreditcard page
     * @return type
     */
    public function capture($params)
    {
        $response = $this->gateway->purchase([
            'amount' => $params["amount"], 
            'currency' => $params["currency"], 
            'card' => [
                'firstName' => $params["clientdetails"]["firstname"],
                'lastName' => $params["clientdetails"]["lastname"],
                'billingAddress1' => $params["clientdetails"]["address1"],
                'billingAddress2' => $params["clientdetails"]["address2"],
                'billingCity' => $params["clientdetails"]["city"],
                'billingPostcode' => $params["clientdetails"]["postcode"],
                'billingState' => $params["clientdetails"]["state"],
                'billingCountry' => $params["clientdetails"]["country"],
                'billingPhone' => $params["clientdetails"]["phonenumber"],
                'shippingAddress1' => $params["clientdetails"]["address1"],
                'shippingAddress2' => $params["clientdetails"]["address2"],
                'shippingCity' => $params["clientdetails"]["city"],
                'shippingPostcode' => $params["clientdetails"]["postcode"],
                'shippingState' => $params["clientdetails"]["state"],
                'shippingCountry' => $params["clientdetails"]["country"],
                'shippingPhone' => $params["clientdetails"]["phone"],
                'company' => $params["clientdetails"]["companyname"],
                'email' => $params["clientdetails"]["email"],
                'number' => $params["cardnum"], 
                'expiryMonth' => substr($params["cardexp"], 0, 2), 
                'expiryYear' => "20".substr($params["cardexp"], 2), 
                'startMonth' => $params["ccexpirymonth"],
                'startYear' => $params["ccexpiryyear"],
                'issueNumber' => $params["cardissuenum"],
                'type' => $params["cctype"],
                'cvv' => $params["cccvv"]
            ]
        ])->send();

        return $this->processResponse($response);
    }
    
    /**
     * Fill configuration form object and load omnipay gateway
     * 
     * @param type $settings
     */
    public function setConfiguration($settings)
    {
        parent::setConfiguration($settings);
        $this->loadGateway();
    }
    
    /**
     * Process response
     * 
     * @param \Omnipay\Common\Message\AbstractResponse $response
     * @return type
     */
    protected function processResponse(\Omnipay\Common\Message\AbstractResponse $response)
    {
        if($response->isRedirect()) 
        {
            $response->redirect();
        } 
        elseif($response->isSuccessful()) 
        {
            $ref = json_decode($response->getTransactionReference());
            $transid = empty($ref) ? $response->getTransactionReference() : $ref->transId;
            
            if($transid === null || $transid === "")
            {
                $data = $response->getData();
                $transid = $data["id"];
            }

            return ["status" => "success", "rawdata" => $response->getData(), "transid" => $transid];
        }

        return ["status" => "error", "rawdata" => $response->getData()];
    }
    
    /**
     * Load form builder configuration based on gateway default params
     * 
     * @throws Exception
     */
    protected function loadForm()
    {
        $status = new Switcher("enabled", "Status");
        $status->addStyle("width", 9);
        
        $displayName = new Text("displayName", "Display Name", "Name that will be displayed on order form", $this->name);
        $displayName->addStyle("width", 9);
        
        $this->configuration = new Form();
        $this->configuration->add($status);
        $this->configuration->add($displayName);
                
        $params = $this->gateway->getDefaultParameters();
        foreach($params as $name => $type)
        {
            if(is_string($type))
            {
                $field = new Text($name, $name, $name, $type);
            }
            elseif(is_bool($type))
            {
                $field = new Switcher($name, $name, $name);
            }
            elseif(is_array($type))
            {
                $field = new Select($name, $name, $name, $type[0], $type);
            }
            else
            {
                throw new Exception("Payment Gateway Error: Unrecognized field provided");
            }
            
            $field->addStyle("width", 9);
            $this->configuration->add($field);
        }
    }   

    /**
     * Load settings from database to OmniPay gateway
     */
    protected function loadGateway()
    {
        $params = $this->gateway->getDefaultParameters();
        foreach(array_keys($params) as $setting)
        {
            $function = "set" . ucfirst($setting);
            $this->gateway->{$function}($this->configuration->get($setting)->value);
        }
    }
}
