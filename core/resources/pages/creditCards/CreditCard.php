<?php

namespace MGModule\ResellersCenter\Core\Resources\Pages\CreditCards;

use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\core\resources\gateways\Factory as PaymentGatewayFactory;
use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice as ResellersCenterInvoice;

use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;

/**
 * Description of CreditCard
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class CreditCard 
{
    /**
     * @var ResellersCenterInvoice
     */
    protected $invoice;

    /**
     * CreditCard page constructor.
     *
     * @param ResellersCenterInvoice $invoice
     */
    public function __construct(ResellersCenterInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Get Credit Card page view
     * @param $vars
     */
    public function getView($vars)
    {
        $decorator = new Decorator();
        return $decorator->getCAPageView($this->invoice, $vars);
    }

    /**
     * Process payment on credit card page
     *
     * @return string|void
     * @throws \Exception
     */
    public function processPayment()
    {
        $gateway = PaymentGatewayFactory::get($this->invoice->reseller->id, $this->invoice->paymentmethod);

        if ($gateway->getType() != "CC") {
            throw new \Exception("Gateway {$this->invoice->paymentmethod} is not CC");
        }
        
        try
        {
            $params = $this->getParamsForCapture();
            $result = $gateway->capture(array_merge($params, $_REQUEST));
            $gateway->log($result["rawdata"], $result["status"]);
            if ($result["status"] != "success") {
                throw new \Exception(Whmcs::lang("invoicepaymentfailedconfirmation"));
            }

            $this->invoice->payments->addTransaction($params["currencyId"], $result["transid"], $params["amount"] ?: 0, $result["fee"] ?: 0, $this->invoice->paymentmethod);
        } catch(\Exception $ex) {
            $this->invoice->sendMessage("Credit Card Payment Failed");
            $errormessage = $ex->getMessage();
            return "<li>{$errormessage}</li>";
        }
    }

    /**
     * Get variables from invoice required for payment
     *
     * @return array
     */
    protected function getParamsForCapture()
    {
        $gateway = PaymentGatewayFactory::get($this->invoice->reseller->id, $this->invoice->paymentmethod);
        $type    = in_array("MGModule\ResellersCenter\core\paymentGateway\interfaces\CCGateway", class_implements($gateway)) ? "CC" : "Invoices";

        $clientdetails          = $this->invoice->client->toArray();
        $clientdetails["model"] = \WHMCS\User\Client::find($this->invoice->userid);

        $result = [
            "companyname"   => $this->invoice->reseller->settings->private->companyName,
            "systemurl"     => Server::getCurrentSystemURL(),
            "langpaynow"    => Whmcs::lang("invoicespaynow"),
            "name"          => $gateway->displayName,
            "type"          => $type,
            "visible"       => "on",
            "paymentmethod" => $this->invoice->paymentmethod,
            "invoiceid"     => $this->invoice->id,
            "invoicenum"    => $this->invoice->invoicenum,
            "amount"        => $this->invoice->total - $this->invoice->amountpaid,
            "description"   => "{$this->invoice->reseller->settings->private->companyName} - " . Whmcs::lang("invoicenumber") . $this->invoice->id,
            "returnurl"     => Server::getCurrentSystemURL() . "rcviewinvoice.php?id={$this->invoice->id}",
            "dueDate"       => $this->invoice->duedate,
            "clientdetails" => $clientdetails,
            "currency"      => $this->invoice->client->currencyObj->code,
            "currencyId"    => $this->invoice->client->currency
        ];

        if (Request::get("ccinfo") == "useexisting")
        {
            $client    = new Client($this->invoice->userid);
            $ccdetails = $client->getCCDetails();

            $result["cardtype"]     = $ccdetails->cardtype;
            $result["lastfour"]     = $ccdetails->cardlastfour;
            $result["cardnum"]      = $ccdetails->cardnum;
            $result["startdate"]    = $ccdetails->startdate;
            $result["cardexp"]      = $ccdetails->expdate;
            $result["cardissuenum"] = $ccdetails->issuenumber;
            $result["gatewayid"]    = $client->gatewayid;
        }
        else
        {
            $cardnum = str_replace(" ", "", trim(Request::get("ccnumber")));
            $result["cardnum"]  = $cardnum;
            $result["lastfour"] = substr($cardnum, -4);
            $result["cardtype"] = Request::get("cctype");
            $result["cardexp"]  = Request::get("ccexpirydate");
        }

        if(!$result["cardnum"] && !$result["gatewayid"] && !$result["cccvv"])
        {
            $this->invoice->sendMessage("Credit Card Payment Due");
        }

        return $result;
    }
}