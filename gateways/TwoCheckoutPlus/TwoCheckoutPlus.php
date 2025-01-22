<?php
namespace MGModule\ResellersCenter\gateways\TwoCheckoutPlus;
use MGModule\ResellersCenter\core\resources\gateways\OmnipayGateway;
use MGModule\ResellersCenter\core\resources\gateways\interfaces\CCGateway;

use MGModule\ResellersCenter\repository\whmcs\Taxes;
use MGModule\ResellersCenter\repository\InvoiceItems;

use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice;
use MGModule\ResellersCenter\core\Server;

/**
 * Description of TwoCheckoutPlus
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class TwoCheckoutPlus extends OmnipayGateway implements CCGateway
{
    public $adminName = "2Checkout";
    
    public $type = "CC";
    
    public function capture($params)
    {
        $invoice = new Invoice($params["invoiceid"]);
        
        try
        {
            $cart = $this->getCart($invoice);
            $this->gateway->setCart($cart);

            $client = [
                "firstName" => $params["clientdetails"]["firstname"],
                "lastName"  => $params["clientdetails"]["lastname"],
                "email"     => $params["clientdetails"]["email"],
                "address1"  => $params["clientdetails"]["address1"],
                "address2"  => $params["clientdetails"]["address2"],
                "city"      => $params["clientdetails"]["city"],
                "state"     => $params["clientdetails"]["state"],
                "postcode"  => $params["clientdetails"]["postcode"],
                "country"   => $params["clientdetails"]["country"],
            ];

            $response = $this->gateway->purchase([
                    "card"          => $client,
                    "transactionId" => $invoice->id,
                    "currency"      => $invoice->client->currencyObj->code,
                    "returnUrl"     => Server::getCurrentSystemURL() . "/viewinvoice.php?id={$invoice->id}"
                ])->send();
        }
        catch (\Exception $ex)
        {
            $this->log($params, $ex->getMessage());
            return;
        }

        //process response
        return $this->processResponse($response);
    }

    public function callback($params)
    {
        try
        {
            //We sent invoice id as transaction id!
            $id      = $params["vendor_order_id"] ? : $params["merchant_order_id"];
            $invoice = new Invoice($id);

            $cart = $this->getCart($invoice);
            $this->gateway->setCart($cart);

            $data = [
                "transactionId" => $invoice->id,
                "currency"      => $invoice->client->currencyObj->code,
                "returnUrl"     => Server::getCurrentSystemURL() . "/viewinvoice.php?id={$invoice->id}"
            ];

            //Validate gateway purchase - this will throw an exception if failed
            $this->gateway->completePurchase($data);

            //Add transaction to invoice
            $transid  = $params["sale_id"] ? : $params["order_number"];
            $amountin = $params["invoice_list_amount"] ? : $params["total"];
            
            $amount = $amountin;
            if(($invoice->total - $amount) <= 0.01 && ($invoice->total - $amount) > 0)
            {
                $amount += 0.01;
            }
            
            $invoice->payments->addTransaction(null, $transid, $amount, 0, $this->name);
        }
        catch (\Exception $ex)
        {
            $this->log($params, $ex->getMessage());
            return;
        }

        $this->log($params, "Success");
        Header("Location: {$params["x_receipt_link_url"]}");
    }
    
    public function refund($params)
    {
        
    }
    
    public function getHeadOutput()
    {
        return "";
    }

    /**
     * Get cart details
     * 
     * @param Invoice $invoice
     * @return int
     */
    private function getCart(\MGModule\ResellersCenter\Core\Resources\Invoices\Invoice $invoice)
    {
        $cart = [];
        foreach ($invoice->items as $item)
        {
            //Fix discounts
            $discounts = [InvoiceItems::TYPE_GROUP_DISCOUNT, InvoiceItems::TYPE_PROMO_DOMAIN, InvoiceItems::TYPE_PROMO_HOSTING];
            if (in_array($item->type, $discounts))
            {
                $item->type = "coupon";
                $item->amount *= -1;
            }

            $amount = $item->taxed ? $this->includeTax($item->amount, $invoice->client) : $item->amount;
            $cart[] = [
                "name"       => $item->description,
                "quantity"   => 1,
                "type"       => $item->type,
                "price"      => $amount,
                "product_id" => $item->relid
            ];
        }

        return $cart;
    }

    private function includeTax($price, $client)
    {
        global $CONFIG;
        if ($CONFIG["TaxType"] == 'Exclusive')
        {
            $repo = new Taxes();
            $tax1 = $repo->getTax(1, $client->state, $client->country);
            $tax2 = $repo->getTax(2, $client->state, $client->country);

            $tax1price = (float) $price * ($tax1->taxrate / 100);
            $tax2price = (float) $price * ($tax2->taxrate / 100);

            $price += ($tax1price + $tax2price);
        }

        return $price;
    }
}
