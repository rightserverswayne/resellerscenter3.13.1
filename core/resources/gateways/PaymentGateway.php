<?php
namespace MGModule\ResellersCenter\Core\Resources\gateways;
use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice;
use MGModule\ResellersCenter\Core\Resources\Pages\CreditCards\CreditCard;
use MGModule\ResellersCenter\repository\whmcs\Currencies;
use MGModule\ResellersCenter\core\resources\gateways\PaymentGatewayLog;

use MGModule\ResellersCenter\core\form\fields\Text;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;

/**
 * Description of PaymentGateway
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
abstract class PaymentGateway
{    
    /**
     * Gateway name
     * 
     * @var string 
     */
    public $name;
    
    /**
     * Gateway type (ex. Invoice, CC)
     * 
     * @var string 
     */
    public $type;

    const PAYMENTTYPE_REMOTECREDITCARD = 'RemoteCreditCard';
    
    /**
     * Gateway configuration
     *
     * @var MGModule\ResellersCenter\core\form\Form
     */
    protected $configuration;

    //Name used by WHMCS
    protected static string $sysName = "";
    
    /**
     * Required in all Payment Gateways
     */
    public function __construct() 
    {
        $this->name = (new \ReflectionClass($this))->getShortName();
        
        //Required in all Gateways
        $order = new Text("order", "");
        $order->addStyle("classes", "hidden");
        
        $this->configuration->add($order);
    }
    
    /**
     * Get value from gateway configuration 
     * 
     * @param type $name
     * @return type
     */
    public function __get($name) 
    {
        $element = $this->configuration->get($name);
        return $element->value;
    }

    /**
     * Set value in gateway configuration
     * 
     * @param type $name
     * @param type $value
     */
    public function __set($name, $value) 
    {
        $this->configuration->set($name, $value);
    }

    public function getSysName(): string
    {
        return static::$sysName;
    }

    public function compareName($name): bool
    {
        return $this->name == $name || $this->getSysName() == $name;
    }

    public function getNormalisedName(): string
    {
        return empty($this->getSysName()) ? $this->name : $this->getSysName();
    }

    /**
     * Log payment gateway response
     * 
     * @param type $data
     * @param type $result
     */
    public function log($data, $result = "")
    {
        $gateway = $this->name;
        $date = date("Y-m-d H:i:s");
        $reseller = ResellerHelper::getByCurrentURL();
        
        $log = new PaymentGatewayLog(null);
        $log->create(["reseller_id" => $reseller->id, "date" => $date, "gateway" => $gateway, "data" => print_r($data, 1), "result" => $result]);
    }
    
    public function getType()
    {
        $implements = class_implements($this);
        if(in_array("MGModule\ResellersCenter\Core\Resources\Gateways\Interfaces\CCGateway", $implements))
        {
            return "CC";
        }
        
        return "Invoices";
    }

    public function orderFormCheckout(Invoice $invoice)
    {
        //If the gateway is CreditCard type then try to make payment
        if ($this->getType() == "CC")
        {
            $creditcard = new CreditCard($invoice);
            $creditcard->processPayment();
        }

        //Currently we only support CC gateways on order checkout
        throw new \Exception("Selected Gateway is not CC");
    }
    
    /**
     * Get HTML configuration
     * 
     * @param $keys - specify array keys for form names
     * @return type
     */
    public function config($keys)
    {
        return $this->configuration->getHTML($keys);
    }
    
    public function getConfiguration()
    {
        $result = array();
        foreach($this->configuration as $item)
        {
            $result[$item->name] = $item->value; 
        }
        
        return $result;
    }
    
    public function getConfigrationValue($name)
    {
        $result = $this->configuration->get($name);
        return $result;
    }
    
    public function setConfiguration($settings)
    {
        foreach($settings as $name => $value)
        {
            $this->configuration->set($name, $value);
        }
    }

    protected function getCurrenciesOptions()
    {
        $repo = new Currencies();
        $currencies = $repo->getAvailableCurrencies();

        $result = array(0 => "None");
        foreach($currencies as $currency)
        {
            $result[$currency->id] = $currency->code;
        }

        return $result;
    }

    public function getPaymentType()
    {
        if(property_exists($this, 'paymentType'))
        {
            return $this->paymentType;
        }
        return false;
    }
}