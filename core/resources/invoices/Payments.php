<?php

namespace MGModule\ResellersCenter\Core\Resources\Invoices;

use MGModule\ResellersCenter\Core\EventManager;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;
use MGModule\ResellersCenter\repository\Invoices;
use MGModule\ResellersCenter\Core\Helpers\Helper;

use MGModule\ResellersCenter\Core\Resources\Transactions\Transaction;
use MGModule\ResellersCenter\Core\Resources\Gateways\PaymentGateway;
use MGModule\ResellersCenter\Core\Resources\Gateways\Factory as PaymentGatewayFactory;

/**
 * Description of Payments.php
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Payments
{
    /**
     * @var Invoice
     */
    protected $invoice;

    /**
     * Payments object for ResellerCenterInvoices
     *
     * @param Invoice $invoice
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return PaymentGateway
     */
    public function getGateway()
    {
        return PaymentGatewayFactory::get($this->invoice->reseller->id, $this->invoice->paymentmethod);
    }

    /**
     * @param $currencyid
     * @param $transid
     * @param $amount
     * @param $fees
     * @param $gateway
     * @param string $date
     * @throws \Exception
     */
    public function addTransaction($currencyid, $transid, $amount, $fees, $gateway, $date = "")
    {
        if($amount <= 0 && $fees <= 0)
        {
            throw new \Exception("emptyAmountOrFee");
        }

        $date = $date ?: date("Y-m-d H:i:s");

        $transaction = new Transaction(); 
        $transaction->create($transid, "Invoice Payment", $this->invoice->userid, $gateway, $currencyid, $date, $amount, $fees, $this->invoice->id, 0);
        EventManager::call("rcInvoiceAddPayment", $this->invoice->id);

        //Reload to get new transaction and check if invoice is paid
        $invoice = new Invoice($this->invoice->id);
        if($invoice->amountpaid >= $invoice->total && $invoice->status == Invoices::STATUS_UNPAID)
        {
            $invoice->updateStatus(Invoices::STATUS_PAID);
        }
        $this->invoice = $invoice;
        
        $gateway = $this->invoice->payments->getGateway();
        if(is_object($gateway))
        {
            $template = $gateway->getType() == "CC" ? "Credit Card Payment Confirmation" : "Invoice Payment Confirmation";
            $this->invoice->sendMessage($template);
        }
    }

    /**
     * Apply credits to Resellers Center Invoice
     *
     * @param $amount
     * @param bool $takeFromClient
     * @throws \MGModule\ResellersCenter\mgLibs\exceptions\WhmcsAPI
     */
    public function applyCredits($amount)
    {
        $clientCredits = $this->invoice->client->credit;

        if( $clientCredits <= 0 )
        {
            return;
        }

        if( $clientCredits - $amount < 0 )
        {
            $amount = $clientCredits;
        }
        
        if($amount > $this->invoice->total)
        {
            $amount = $this->invoice->total;
        }

        if($amount > 0)
        {
            //Apply credits to Resellers Center Invoice
            $this->invoice->credit += $amount;
            $this->invoice->total  -= $amount;
            $this->invoice->save();

            //Transfer credits to Reseller
            WhmcsAPI::request("AddCredit", [
                "clientid" => $this->invoice->reseller->client->id,
                "description" => "Client payment for Invoice ID: {$this->invoice->id}",
                "amount" => number_format(Helper::calcCurrencyValue($amount, $this->invoice->client->currency, $this->invoice->reseller->client->currency), 2)
            ]);

            //Take credits from client
            $description = "Credit payment for Invoice ID: {$this->invoice->id}";
            $this->invoice->client->credits->remove($amount, $description);
        }

        //Check if invoice is paid
        if($this->invoice->amountpaid >= $this->invoice->total && $this->invoice->status == Invoices::STATUS_UNPAID)
        {
            $this->invoice->updateStatus(Invoices::STATUS_PAID);
        }
    }
}