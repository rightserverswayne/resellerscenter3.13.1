<?php
namespace MGModule\ResellersCenter\Core\Resources\Invoices;
use MGModule\ResellersCenter\Core\Resources\ResourceObject;
use MGModule\ResellersCenter\models\InvoiceItem;

use MGModule\ResellersCenter\repository\InvoiceItems;

/**
 * Description of Item
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Item extends ResourceObject
{
    protected function getModelClass()
    {
        return InvoiceItem::class;
    }

    public function create($resellerid, $invoiceid, $userid, $type, $relid, $description, $amount, $taxed, $duedate, $paymentmethod, $notes = "")
    {
        $repo = new InvoiceItems();
        $newItemId  = $repo->create(array(
            "reseller_id"   => $resellerid,
            "invoice_id"    => $invoiceid,
            "userid"        => $userid,
            "type"          => $type,
            "relid"         => $relid,
            "description"   => $description,
            "amount"        => $amount,
            "taxed"         => $taxed,
            "duedate"       => $duedate,
            "paymentmethod" => $paymentmethod,
            "notes"         => $notes,
        ));
        
        //load model
        $this->model = $repo->find($newItemId);
        return $this;
    }
    
    public function getTaxValue()
    {
        global $CONFIG;

        $result = $this->amount * $this->invoice->taxrate / 100;
        if($CONFIG["TaxType"] == "Inclusive")
        {
            $amount = $this->amount / (1 + $this->invoice->taxrate/100 + $this->invoice->taxrate2/100);
            $result = $amount * $this->invoice->taxrate / 100;
        }

        return $result;
    }
    
    public function getTax2Value()
    {
        global $CONFIG;

        $result = $this->amount * $this->invoice->taxrate2 / 100;
        if($CONFIG["TaxType"] == "Inclusive")
        {
            $amount = $this->amount / (1 + $this->invoice->taxrate/100 + $this->invoice->taxrate2/100);
            $result = $amount * $this->invoice->taxrate2 / 100;
        }
        
        return $result;
    }
}