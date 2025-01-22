<?php
namespace MGModule\ResellersCenter\Core\Resources\Invoices;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;

use MGModule\ResellersCenter\Core\Resources\ResourceObject;
use MGModule\ResellersCenter\Core\Traits\HasModel;
use MGModule\ResellersCenter\Core\Traits\HasProperties;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\Core\Whmcs\Orders\Order;
use MGModule\ResellersCenter\repository\EmailTemplates;
use MGModule\ResellersCenter\repository\Invoices;
use MGModule\ResellersCenter\repository\InvoiceItems;

use MGModule\ResellersCenter\repository\whmcs\EmailTemplates as EmailTemplatesRepository;
use MGModule\ResellersCenter\repository\whmcs\Orders;
use MGModule\ResellersCenter\repository\whmcs\Invoices as WhmcsInvoices;
use MGModule\ResellersCenter\Core\Whmcs\Invoices\Invoice as WhmcsInvoice;

use MGModule\ResellersCenter\core\Mailer;
use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\repository\whmcs\EmailTemplates as WHMCSEmailTemplates;

/**
 * Description of Invoice
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Invoice extends ResourceObject
{
    use HasModel, HasProperties
    {
        HasProperties::__get as hasPropertiesGet;
        HasModel::load as hasModelLoad;
    }

    /**
     * @var Reseller
     */
    protected $reseller;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Payments
     */
    protected $payments;

    /**
     * @var View
     */
    protected $view;

    /**
     * @var Pdf
     */
    protected $pdf;

    /**
     * @var ItemList
     */
    protected $items;

    /**
     * @return string
     */
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\models\Invoice::class;
    }

    /**
     * @param $name
     * @return Reseller
     * @throws \Exception
     */
    public function __get($name)
    {
        if($name == "items")
        {
            if(empty($this->items) && !empty($this->model->items))
            {
                $this->items = new ItemsList();
                foreach($this->model->items as $item)
                {
                    $this->items->add(new Item($item->id));
                }
            }

            $result = $this->items;
        }
        else
        {
            $result = $this->hasPropertiesGet($name);
        }

        return $result;
    }

    /**
     * Create new Resellers Center Invoice
     *
     * @param $userid
     * @param $date
     * @param $duedate
     * @param $paymentmethod
     * @return Invoice
     */
    public function create($resellerid, $invoicenum, $userid, $date, $duedate, $status, $paymentmethod, $tax1 = 0, $tax2 = 0)
    {
        $classname = $this->getModelClass();
        $this->model = new $classname();

        $this->reseller_id = $resellerid;
        $this->invoicenum = $invoicenum;
        $this->userid = $userid;
        $this->date = $date;
        $this->duedate = $duedate;
        $this->status = $status;
        $this->paymentmethod = $paymentmethod;
        $this->taxrate = $tax1 ?: 0;
        $this->taxrate2 = $tax2 ?: 0;
        $this->save();

        EventManager::call("rcInvoiceCreated", $this->model->id);
        return $this;
    }

    /**
     * Update Invoices fields and items
     * 
     * @param type $data
     */
    public function update($data)
    {
        //Just in case
        unset($data["invoiceid"]);

        $this->date = $data["date"];
        $this->duedate = $data["duedate"];
        $this->save();

        foreach($this->model->items as $item)
        {
            $item->description = preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, html_entity_decode($data["itemdescription"][$item->id]));;
            $item->amount = $data["itemamount"][$item->id];
            $item->taxed = $data["itemtaxed"][$item->id];
                    
            $item->save();
        }

        $this->updateInvoiceTotals();

        EventManager::call("rcInvoiceUpdated", $this->model->id);
    }

    /**
     * Change invoice status
     * If status will be changed to paid - markAsPaid method will be fired
     * 
     * @param type $status
     * @throws Exception
     */
    public function updateStatus($status)
    {
        if(!in_array($status, Invoices::STATUSES))
        {
            throw new Exception("Invalid status provided");
        }

        $this->status = $status;
        $this->save();
        
        //Activate all related hosting and addons if invoice has been paid
        if($status == Invoices::STATUS_PAID)
        {
            $this->markAsPaid();
            $this->sendMessage("Invoice Payment Confirmation");
        }
    }
    
    public function getPaymentMethod()
    {
        $gateways = Helper::getCustomGateways($this->model->reseller->id);
        foreach($gateways as $gateway)
        {
            if($gateway->compareName($this->model->paymentmethod))
            {
                return $gateway;
            }
        }

        return false;
    }
    
    /**
     * Update invoice payment method
     * 
     * @param type $name
     * @return type
     * @throws \Exception
     */
    public function updatePaymentMethod($name)
    {
        //Check if valid gateway has been selected
        $gateways = Helper::getCustomGateways($this->model->reseller->id);
        foreach($gateways as $gateway)
        {
            if($gateway->enabled && $gateway->compareName($name))
            {
                $this->model->paymentmethod = $name;
                $this->model->save();
                return;
            }
        }

        throw new \Exception("Selected gateway is not valid");
    }

    /**
     * Mark loaded invoice as paid - this also accept order 
     * and (in case when this is mass payment invoice) mark as paid all related invoices
     */
    public function markAsPaid()
    {
        EventManager::call("rcInvoicePaid", $this->model->id);
        //If there is no WHMCS invoice related
        if($this->isParentInvoice())
        {
            //mark paid and activate all related invoices
            foreach($this->model->items as $item)
            {
                $invoicenum = $this->invoicenum ? $this->invoicenum : $this->id;
                $transid = \MGModule\ResellersCenter\mgLibs\Lang::absoluteT("massPaymentTransactionDescription"). $invoicenum;

                $rcInvoice = new Invoice($item->relid);
                $rcInvoice->__get("payments")->addTransaction($this->client->currency, $transid, $rcInvoice->total - $rcInvoice->amountpaid, 0, $this->paymentmethod);
            }
        }
        else
        {
            $this->datepaid = date('Y-m-d H:i:s');
            $this->status = Invoices::STATUS_PAID;
            $this->save();

            if($this->whmcsInvoice != null)
            {
                $this->markPaidRelatedInvoice($this->whmcsInvoice->id);
            }
        }
    }
    
    /**
     * Mark as paid single invoice
     * Activate related order and services
     * 
     * @param type $relid
     */
    public function markPaidRelatedInvoice($relid)
    {
        //Auto pay for WHMCS <-> Reseller invoice
        try
        {
            $invoice = new WhmcsInvoice($relid);
            $reseller = $this->__get("reseller");

            if($invoice->status == WhmcsInvoices::STATUS_UNPAID && $reseller->settings->private->autoWhmcsInvoicePayment)
            {
                $invoice->addCreditPayment($invoice->total);
            }
        }
        catch(\Exception $ex)
        {
            EventManager::call($ex->getMessage());
        }

        //Accept WHMCS order
        if($invoice->order->status == Orders::STATUS_PENDING && $invoice->status == WhmcsInvoices::STATUS_PAID)
        {
            $order = new Order($invoice->order);
            $order->activate();
        }
    }
    
    /**
     * Mark as paid for zero 
     * Activate related order and services
     * 
     * @param type $relid
     */
    public function markAsPaidIfZero()
    {
        if($this->total == 0.00)
        {
            $this->updateStatus(Invoices::STATUS_PAID);
        }
    }
    
    /**
     * Update invoice totals
     * 
     * @global type $CONFIG
     */
    public function updateInvoiceTotals()
    {
        global $CONFIG;

        $tax = $tax2 = 0;
        $subtotal = $total = 0;
        foreach($this->model->items as $raw)
        {
            $subtotal += $raw->amount;
            if($raw->taxed && !$this->model->client->taxexempt && $CONFIG["TaxEnabled"])
            {
                $item = new Item($raw);
                $tax += $currentTax = $item->getTaxValue();
                $tax2 += $currentTax2 = $item->getTax2Value();    
                
                if($CONFIG["TaxType"] == "Inclusive") 
                {
                    $subtotal -= $currentTax + $currentTax2;
                }
            }
        }
        
        $this->tax = $tax;
        $this->tax2 = $tax2;
        $this->subtotal = $subtotal;
        $this->total = $subtotal + $tax + $tax2;
        $this->save();
    }
    
    /**
     * Check if this invoice has been included in mass payment invoice
     * 
     * @return boolean
     */
    public function getParentInvoice()
    {
        $repo = new InvoiceItems();
        $item = $repo->getByTypeAndRelid(InvoiceItems::TYPE_INVOICE, $this->id);
         
        //There can be only one parent invoice in the system
        $parentInvoice = new Invoice($item[0]->relid);
        
        return $parentInvoice;
    }
    
    /**
     * Check if this is mass payment invoice
     * 
     * @return boolean
     */
    public function isParentInvoice()
    {
        if(empty($this->model))
        {
            $this->load();
        }

        foreach($this->model->items as $item)
        {
            if($item->type == InvoiceItems::TYPE_INVOICE)
            {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Send invoice related message
     * This method is using PHPMailer to send messages
     *
     * @param string $tplName
     */
    public function sendMessage( $tplName )
    {
        $whmcsTemplates = new WHMCSEmailTemplates();
        $available = $whmcsTemplates->getByName($tplName, "");

        if (!$this->reseller->exists) {
            $this->load();
            $this->reseller = new Reseller($this->model->reseller);
        }

        if ($available->disabled || !$this->reseller->settings->admin->emailTemplates[$tplName]) {
            return;
        }
        
        $invoicePdf = new Pdf($this);
        $filename   = $invoicePdf->saveFile();

        $templates = new EmailTemplates();
        $template  = $templates->getByName($this->reseller->id, $tplName, $this->client->language);
        if( empty($template) )
        {
            $whmcsTemplates = new EmailTemplatesRepository();
            $template       = $whmcsTemplates->getByName($tplName, $this->client->language);
        }

        $reciever = [
            'userid' => $this->client->id,
            'email' => $this->client->email,
            'name' => $this->client->firstname,
            $this->client->lastname
        ];

        $sender   = [
            'email' => $this->reseller->settings->private->email,
            'name'  => $this->reseller->settings->private->companyName
        ];

        $fields = new \MGModule\ResellersCenter\core\MergeFields();
        $params = $fields->getFieldsValues($this->reseller->id, $this->client->id, null, null, null, null, $this->model->id);

        Mailer::sendMail($this->reseller, $template, $reciever, $sender, [], $params, [$filename]);
    }

    /**
     * Override properties classes
     *
     * @return array
     */
    protected function getOverriddenPropertiesClasses()
    {
        $this->model ?: $this->load();

        return
        [
            "reseller" =>
            [
                "class" => Reseller::class,
                "model" => $this->model->reseller
            ],
            "client" =>
            [
                "class" => Client::class,
                "model" => $this->model->client
            ],
            "payments" =>
            [
                "class" => Payments::class
            ],
            "view" =>
            [
                "class" => View::class
            ],
            "pdf" =>
            [
                "class" => Pdf::class
            ],
        ];
    }
}
