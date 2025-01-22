<?php
namespace MGModule\ResellersCenter\gateways\Stripe;
use MGModule\ResellersCenter\core\resources\gateways\PaymentGateway;
use MGModule\ResellersCenter\core\resources\gateways\interfaces\CCGateway;
use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice;

use MGModule\ResellersCenter\core\Server;
use MGModule\ResellersCenter\core\form\Form;
use MGModule\ResellersCenter\core\form\fields\Text;
use MGModule\ResellersCenter\core\form\fields\Switcher;

use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\gateways\Stripe\Components\Models\StripeRequest;
use MGModule\ResellersCenter\gateways\Stripe\Components\Submodule\Stripe3\StripeServices;
use MGModule\ResellersCenter\gateways\Stripe\Components\Submodule\SubmoduleFactory;
use MGModule\ResellersCenter\mgLibs\Smarty;

/**
 * Description of Stripe
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Stripe extends PaymentGateway implements CCGateway
{
    public $adminName = "Stripe";
    
    public $type = "CC";

    public $paymentType = PaymentGateway::PAYMENTTYPE_REMOTECREDITCARD;

    const GATEWAY_NAME = "Stripe";

    protected static string $sysName = 'stripe';
    
    //Set configuration form
    public function __construct() 
    {
        $status = new Switcher("enabled", "Status");
        $status->addStyle("width", 9);
        
        $displayName = new Text("displayName", "Display Name", "Name that will be displayed on order form", "Stripe");
        $displayName->addStyle("width", 9);
        
        $secretApiKey = new Text("secretApiKey", "Secret API Key", "Your secret API Key ensures only communications from Stripe are validated");
        $secretApiKey->addStyle("width", 9);
        
        $publishableApiKey = new Text("publishableApiKey", "Publishable API Key", "Your publishable API key identifies your website to Stripe during communications");
        $publishableApiKey->addStyle("width", 9);
        
        $statementDescriptor = new Text("statementDescriptor", "Statement Descriptor", "Displayed on your customer's credit card statement (Maximum of 22 characters)");
        $statementDescriptor->addStyle("width", 9);
        
        $this->configuration = new Form();
        $this->configuration->add($status);
        $this->configuration->add($displayName);
        $this->configuration->add($secretApiKey);
        $this->configuration->add($publishableApiKey);
        $this->configuration->add($statementDescriptor);

        parent::__construct();
    }
    
    public function link(\MGModule\ResellersCenter\Core\Resources\Invoices\Invoice $invoice)
    {
        global $whmcs;

        $params = array(
            "whmcsUrl" => Server::getCurrentSystemURL(),
            "invoiceid" => $invoice->id,
            "payNowText" => $whmcs->get_lang("invoicespaynow"),
        );

        $html = Smarty::I()->view("StripeBtn", $params, __DIR__);
        return $html;
    }

    public function capture($params)
    {
        $invoice = new Invoice($params["invoiceid"]);
        $amount = $invoice->total - $invoice->amountpaid;
        $currency = $invoice->client->currencyObj;

        $data = Request::get();
        $data['amount']  = $data['amount'] ?: (int)round($amount * 100);
        $data['currency']  = $data['currency'] ?: strtolower($currency->code);
        $data['statementDescription']  = $data['statementDescription'] ?: $this->getChargeDescription($invoice);
        $data['invoicenum'] =  $data['invoicenum'] ?: (empty($invoice->invoicenum) ? $invoice->id : $invoice->invoicenum);
        $data['paymentmethod'] = strtolower($invoice->paymentmethod);

        /* @var $model StripeRequest*/
        $model = StripeRequest::build($data);

        $service = SubmoduleFactory::create(SubmoduleFactory::STRIPE_VERSION_3);
        $service::setApiKey($this->secretApiKey);
        /* @var $service StripeServices */
        $service->setModel($model);

        $service->isValid();
        $result = $service->capture();

        if(empty($CONFIG["CCNeverStore"]) && empty($invoice->client->gatewayid) && $model->getCcinfo() == 'new')
        {
            $remoteToken = json_encode([ "method" => $service->getPaymentMethodId(), "customer" => $service->getCustomerId()]);
            $this->saveCustomerCreditCard($invoice->client->id, $remoteToken, $result['rawdata']['charge']['payment_method_details']['card'], $model->getCcdescription());
        }

        return $result;
    }
    
    public function getHeadOutput() 
    {
        $output = "<script type='text/javascript' data-attr='StripeRC' src='https://js.stripe.com/v3/'></script>";

        $output .= $this->getApiConstants();

        $output .=
                    "<script type='text/javascript' data-attr='StripeRC' src='modules/addons/ResellersCenter/gateways/Stripe/stripeModule.js'></script>"
                    . "<script type='text/javascript' data-attr='StripeRC' src='modules/addons/ResellersCenter/gateways/Stripe/stripeRC.js'></script>";

        return $output;
    }

    private function getApiConstants()
    {
        global $whmcs;

        $publishableKey = (string) $this->configuration->get("publishableApiKey")?:'empty';

        $apiVersion = \WHMCS\Module\Gateway\Stripe\Constant::$apiVersion ? \WHMCS\Module\Gateway\Stripe\Constant::$apiVersion : '2019-03-14';
        $jsConsts = "<script type='text/javascript'>
                var 
                 stripe = null,
                 card = null;            
                 lang = {
                     creditCardInput: '" . $whmcs->get_lang("creditcardcardnumber") . "',
                     creditCardExpiry: '" . $whmcs->get_lang("creditcardcardexpires") . "',
                     creditCardCvc: '" . $whmcs->get_lang("creditcardcvvnumbershort") . "'                     
                 };
                $(document).ready(function() {
                    stripe = Stripe('{$publishableKey}');
                    stripe.api_version = '{$apiVersion}';
                    elements = stripe.elements();
                    elementOptions = {
                        style: {
                            base: {},
                        },
                    };
                        card                = elements.create('cardNumber', elementOptions);
                        cardExpiryElements  = elements.create('cardExpiry', elementOptions);
                        cardCvcElements     = elements.create('cardCvc', elementOptions);
                    });
                    </script>";

        return $jsConsts;


    }

    private function getChargeDescription($invoice)
    {
        $invoicenum = $invoice->invoicenum ?: $invoice->id;
        
        //swap avaliable vars
        $this->statementDescriptor = str_replace("{companyName}", $invoice->reseller->settings->private->companyName, $this->statementDescriptor);
        $this->statementDescriptor = str_replace("{invoiceid}", $invoicenum, $this->statementDescriptor);
        
        return $this->statementDescriptor;
    }
    
    private function saveCustomerCreditCard($userid, $paymentId, $cardDetails, $ccDescription)
    {
        $client = new Client($userid);

        $client->updateCCDetails(
            $cardDetails['brand'],
            $cardDetails['last4'],
            "{$cardDetails['exp_month']}/{$cardDetails['exp_year']}",
            null,
            null,
            null,
            $paymentId,
            true,
            $ccDescription,
            strtolower($this->name)
        );
    }
}
