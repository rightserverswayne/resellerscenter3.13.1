<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\core\resources\gateways\Factory as PaymentGatewayFactory;
use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice as ResellersCenterInvoice;

use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\core\Server;

/**
 * Description of ClientAreaFooterOutput
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ClientAreaFooterOutput
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
        $this->functions[PHP_INT_MAX] = function($params)
        {
            return $this->addGatewaysHeadOutput($params);
        };
    }

    /**
     * @return string|void
     */
    public function addGatewaysHeadOutput()
    {
        $reseller = Reseller::getCurrent();

        if(!$reseller->exists || !$reseller->settings->admin->resellerInvoice || (basename(Server::get("SCRIPT_NAME")) != "rccreditcard.php" && basename(Server::get("SCRIPT_NAME")) != "cart.php"))
        {
            return;
        }
        if(basename(Server::get("SCRIPT_NAME")) == "rccreditcard.php")
        {
            //for rccreditcard.php form only
            $invoiceid = Request::get("invoiceid");
            $invoice = new ResellersCenterInvoice($invoiceid);

            $gateway = PaymentGatewayFactory::get($invoice->reseller->id, $invoice->paymentmethod);

            return $gateway->getHeadOutput();
        }
        
        $gateway = null;
        $stripe = PaymentGatewayFactory::get($reseller->id, 'Stripe');

        if($stripe->enabled == 'on')
        {
            /**
             * remove WHMCS stripe js files because they override RC stripe js
             *
             * RC stripe script must contain 'RC' somewhere so it will not be removed, eg  data-attr='StripeRC'
             */
            global $smarty;
            $smarty->register_outputfilter(function($vars){
                if(strpos($vars, 'stripe') !== false)
                {
                    /**
                     * replaces script between tags <script> </script> which does not contain 'RC' and contains 'stripe'
                     */
                    $result = preg_replace("/(<script.[^RC]*stripe.*?>)(?:<\/script>)*/m", '', $vars);
                    /**
                     * replace WHMCS stripe js, it starts with var card = null,
                     */
                    $result = preg_replace("/(var\scard\s=\snull\,.+?(?:\}\);))/s", '', $result);

                    return $result;
                }
                return $vars;
            });
            $gateway = $stripe;
        } elseif(basename(Server::get("SCRIPT_NAME")) == "cart.php") {
            return;
        }
        if(!$gateway)
        {
            $invoiceid = Request::get("invoiceid");
            $invoice = new ResellersCenterInvoice($invoiceid);
            $gateway = PaymentGatewayFactory::get($invoice->reseller->id, $invoice->paymentmethod);
        }

        return $gateway->getHeadOutput();
    }
}
