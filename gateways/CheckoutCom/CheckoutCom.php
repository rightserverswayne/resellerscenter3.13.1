<?php
namespace MGModule\ResellersCenter\gateways\CheckoutCom;
use MGModule\ResellersCenter\core\resources\gateways\OmnipayGateway;
use MGModule\ResellersCenter\core\resources\gateways\interfaces\InvoiceGateway;

use MGModule\ResellersCenter\repository\Transactions;
use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice;

use MGModule\ResellersCenter\core\Server;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\mgLibs\Smarty;

/**
 * Description of CheckoutCom
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class CheckoutCom extends OmnipayGateway implements InvoiceGateway
{
    public $adminName = "CheckoutCom";
    
    public $type = "Invoices";

    public function link(\MGModule\ResellersCenter\Core\Resources\Invoices\Invoice $invoice)
    {
        $currency = trim($invoice->client->currencyObj->code);
        $amount   = $invoice->total - $invoice->amountpaid;
        $response = $this->gateway->purchase([
                        "amount"   => $amount,
                        "currency" => $currency,
                        "metaData" => [
                            "invoiceid" => $invoice->id
                        ],
                    ])->send();
        
        $params = [
            "successurl" => Server::getCurrentSystemURL()."viewinvoice.php?id={$invoice->id}",
            "apikey"     => $this->publicApiKey,
            "token"      => $response->getTransactionReference(),
            "email"      => $invoice->client->email,
            "amount"     => $amount * 100,
            "currency"   => $currency,
            "langpaynow" => Whmcs::lang("invoicespaynow")
        ];
            
        $dir = ROOTDIR.DS."modules".DS."addons".DS."ResellersCenter".DS."gateways".DS.$this->name;
        return Smarty::I()->view("{$this->name}Btn", $params, $dir);
    }

    public function callback($params)
    {
        $json = file_get_contents('php://input');
        $obj  = json_decode($json);
        
        if ($obj->eventType != "charge.captured")
        {
            return;
        }

        try
        {
            $response = $this->gateway->completePurchase(['amount' => $obj->message->value, 'transactionReference' => $obj->message->originalId])->send();
            if ($response->isSuccessful())
            {

                $repo        = new Transactions();
                $transaction = $repo->getByTransId($obj->message->originalId);

                if (! $transaction->exists)
                {
                    $invoice = new Invoice($obj->message->metadata->invoiceid);

                    $amount = $obj->message->value / 100;
                    $invoice->payments->addTransaction(null, $obj->message->originalId, $amount, 0, $this->name);
                }

                $this->log($obj, "Success");
            }
        }
        catch (\Exception $ex)
        {
            $this->log($obj, $ex->getMessage());
        }
    }
    
    public function refund($params)
    {
        
    }
}
