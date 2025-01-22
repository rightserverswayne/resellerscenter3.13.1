<?php
namespace MGModule\ResellersCenter\Gateways\BillPlzPay;
use MGModule\ResellersCenter\Core\Resources\gateways\PaymentGateway;
use MGModule\ResellersCenter\core\resources\gateways\interfaces\InvoiceGateway;

use MGModule\ResellersCenter\core\form\fields\Textarea;
use MGModule\ResellersCenter\core\form\fields\Switcher;
use MGModule\ResellersCenter\core\form\fields\Select;
use MGModule\ResellersCenter\core\form\fields\Text;
use MGModule\ResellersCenter\core\form\Form;

use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice;
use MGModule\ResellersCenter\mgLibs\Smarty;
use MGModule\ResellersCenter\core\Server;

class BillPlzPay extends PaymentGateway implements InvoiceGateway
{
    public $adminName = "Billplz";

    public $type = "Invoices";

    //Set configuration form
    public function __construct()
    {
        $status = new Switcher("enabled", "Status");
        $status->addStyle("width", 9);

        $displayName = new Text("displayName", "Display Name", "Name that will be displayed on order form", "Billplz Payment");
        $displayName->addStyle("width", 9);

        $apiKey = new Text("apiKey", "API Secret Key");
        $apiKey->addStyle("width", 9);

        $collectionId = new Text("collectionId", "Collection ID", "Optional. If you unsure, leave blank.");
        $collectionId->addStyle("width", 9);

        $xSignatureKey = new Text("xSignatureKey", "X Signature Key");
        $xSignatureKey->addStyle("width", 9);

        $instructions = new Textarea("instructions", "Payment Instruction");
        $instructions->addStyle("width", 9);

        $deliver = new Select("deliver", "Deliver Email & SMS", "Note: Charge RM0.15 for every SMS notification sent", 0, "", [
            '0' => 'No Notification',
            '1' => 'Email Notification',
            '2' => 'SMS Notification',
            '3' => 'Email & SMS Notification'
        ]);
        $deliver->addStyle("width", 9);

        $succesPath = new Select("successPath", "Successful Payment", "Choose page to redirect the user after completed payment", 0, "", [
            'viewinvoice' => 'Specific Invoice',
            'listinvoice' => 'List Invoice',
            'clientarea' => 'Client Area',
        ]);
        $succesPath->addStyle("width", 9);

        $failedPath = new Select("failedPath", "Failed Payment", "Choose page to redirect the user after failed payment", 0, "", [
            'listinvoice' => 'List Invoice',
            'viewinvoice' => 'Specific Invoice',
            'clientarea' => 'Client Area',
        ]);
        $failedPath->addStyle("width", 9);

        $options = $this->getCurrenciesOptions();
        $convertto = new Select("convertto", "Convert To For Processing", "", 0, "", $options);
        $convertto->addStyle("width", 9);

        $this->configuration = new Form();
        $this->configuration->add($status);
        $this->configuration->add($displayName);
        $this->configuration->add($apiKey);
        $this->configuration->add($collectionId);
        $this->configuration->add($xSignatureKey);
        $this->configuration->add($deliver);
        $this->configuration->add($instructions);
        $this->configuration->add($succesPath);
        $this->configuration->add($failedPath);
        $this->configuration->add($convertto);

        parent::__construct();
    }

    public function callback($data)
    {
        require_once __DIR__ . '/core/billplzPay/billplz-api.php';

        // Module name.
        if (!$this->enabled)
        {
            die("Module Not Activated");
        }

        try
        {
            $data = Billplz::getCallbackData($this->xSignatureKey);

            // Validate the status from ID
            $billplz = new Billplz($this->apiKey);
            $moreData = $billplz->check_bill($data['id']);

            if($data['paid'])
            {
                $invoice = new Invoice($moreData["reference_1"]);

                $amount = number_format(($moreData['amount']), 2);
                if($this->convertto)
                {
                    $amount = convertCurrency($amount, $this->convertto, $invoice->client->currency);
                }

                $invoice->addPayment(0, $data['id'], $amount, 0, $this->name);
                $this->log($_POST, "Callback: {$data['state']}");
            }
        }
        catch (\Exception $e)
        {
            exit($e->getMessage());
        }
    }

    public function link(Invoice $invoice)
    {
        $amount = $invoice->total;
        if($this->convertto)
        {
            $amount = convertCurrency($invoice->total, $invoice->client->currency, $this->convertto);
        }

        $raw_string = $amount .$invoice->id. $invoice->userid;
        $filtered_string = preg_replace("/[^a-zA-Z0-9]+/", "", $raw_string);
        $hash = hash_hmac('sha256', $filtered_string, $this->xSignatureKey);

        $params = [
            "resellerid"            => $invoice->reseller->id,
            "email"                 => $invoice->client->email,
            "basecurrencyamount"    => $invoice->total,
            "basecurrency"          => $invoice->client->currencyObj->code,
            "userid"                => $invoice->client->id,
            "mobile"                => $invoice->client->phonenumber,
            "name"                  => "{$invoice->client->firstname} {$invoice->client->lastname}",
            "amount"                => $amount,
            "invoiceid"             => $invoice->id,
            "description"           => "Invoice Payment", //?
            "hash"                  => $hash,
            "instructions"          => $this->instructions,
            "systemUrl"             => Server::getCurrentSystemURL(),
        ];

        $html = Smarty::I()->view("BillPlzPayBtn", $params, __DIR__);
        return $html;
    }

    public function refund($params)
    {
      //Not supported
    }
}