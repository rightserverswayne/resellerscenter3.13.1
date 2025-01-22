<?php
namespace MGModule\ResellersCenter\gateways\WePay;
use MGModule\ResellersCenter\core\resources\gateways\OmnipayGateway;
use MGModule\ResellersCenter\core\resources\gateways\interfaces\CCGateway;

use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice;
use MGModule\ResellersCenter\core\Server;

/** 
 * Description of WePay
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class WePay extends OmnipayGateway implements CCGateway
{
    public $adminName = "WePay";
    
    public function capture($params)
    {
        $response = $this->gateway->purchase([
            'transactionId' => $params["invoiceid"],
            'amount'        => $params["amount"],
            'currency'      => $params["currency"],
            'description'   => "Invoice #{$params["invoiceid"]}",
            'callback_uri'  => Server::getCurrentSystemURL()."modules/addons/ResellersCenter/gateways/callback/WePay.php",
            'returnUrl'     => Server::getCurrentSystemURL()."viewinvoice.php?id={$params["invoiceid"]}",
            "card" => [
                "firstName" => $params["clientdetails"]["firstname"],
                "lastName"  => $params["clientdetails"]["lastname"],
                "email"     => $params["clientdetails"]["email"],
            ]
        ])->send();

        return $this->processResponse($response);
    }
    
    public function callback($params)
    {
        try
        {
            $response = $this->gateway->completePurchase()->sendData($params);

            if($response->isSuccessful())
            {
                $invoice = new Invoice($params["reference_id"]);
                $amount = $invoice->total - $invoice->amountpaid;
                $invoice->payments->addTransaction(null, $params["checkout_id"], $amount, 0, $this->name);
            }
        }
        catch(\Exception $e)
        {
            $this->log($e->getMessage(), "Failed");
        }
    }
    
    public function refund($params)
    {
        
    }

    public function getHeadOutput()
    {
        return "";
    }
}