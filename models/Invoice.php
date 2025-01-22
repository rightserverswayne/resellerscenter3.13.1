<?php

namespace MGModule\ResellersCenter\models;

use \Illuminate\Database\Eloquent\Model as EloquentModel;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Helpers\RcInvoiceNumberingHelper;
use MGModule\ResellersCenter\libs\CreditLine\Helpers\CreditLineOperationLogger;
use MGModule\ResellersCenter\libs\CreditLine\Interfaces\InvoiceModelInterface;
use MGModule\ResellersCenter\repository\Invoices;
use MGModule\ResellersCenter\Repository\Source\AbstractRepository;
use WHMCS\Billing\Tax;

class Invoice extends EloquentModel implements InvoiceModelInterface
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_Invoices';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('reseller_id', 'relinvoice_id', 'userid', 'invoicenum', 'date', 'duedate', 'datepaid', 'last_capture_attempt', 'subtotal', 'credit', 'tax', 'tax2', 'total', 'taxrate', 'taxrate2', 'status', 'paymentmethod', 'notes');

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = array('date', 'duedate', 'datepaid', 'last_capture_attempt');
    
    /**
     * Indicates if the model should soft delete.
     *
     * @var bool
     */
    protected $softDelete = false;
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    
    public function whmcsInvoice()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Invoice", "relinvoice_id");
    }
    
    public function items()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\InvoiceItem", "invoice_id");
    }
    
    public function reseller()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\Reseller", "reseller_id");
    }
    
    public function client()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Client", "userid");
    }
    
    public function transactions()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\Transaction", "invoice_id");
    }
    
    public function getDateAttribute()
    {
        return date("Y-m-d", strtotime($this->attributes["date"]));
    }
    
    public function getDuedateAttribute()
    {
        return date("Y-m-d", strtotime($this->attributes["duedate"]));
    }
    
    public function getDatepaidAttribute()
    {
        return date("Y-m-d H:i:s", strtotime($this->attributes["datepaid"]));
    }
    
    public function getLastCaptureAttemptAttribute()
    {
        return date("Y-m-d H:i:s", strtotime($this->attributes["last_capture_attempt"]));
    }

    public function getAmountpaidAttribute()
    {
        $total = 0;
        foreach($this->transactions as $trans)
        {
            $total += $trans->amountin - $trans->amountout;
        }

        return $total;
    }

    public function getRepository(): AbstractRepository
    {
        return new Invoices();
    }

    function recalculate()
    {
        if (!function_exists("getClientsDetails")) {
            \App::load_function("client");
        }
        $taxEnabled = \WHMCS\Config\Setting::getValue("TaxEnabled");
        $taxPerLineItem = \WHMCS\Config\Setting::getValue("TaxPerLineItem");

        $taxCalculator = new Tax();
        $taxCalculator->setIsInclusive(\WHMCS\Config\Setting::getValue("TaxType") == "Inclusive")->setIsCompound(\WHMCS\Config\Setting::getValue("TaxL2Compound"));
        if (is_numeric($this->taxrate)) {
            $taxCalculator->setLevel1Percentage($this->taxrate);
        }
        if (is_numeric($this->taxrate2)) {
            $taxCalculator->setLevel2Percentage($this->taxrate2);
        }

        $clientsDetails = getClientsDetails($this->userid);
        foreach ($this->items as $item) {
            if ($item->taxed && $taxEnabled && !$clientsDetails["taxexempt"]) {
                if ($taxPerLineItem) {
                    $taxCalculator->setTaxBase($item->amount);
                    $tax += $taxCalculator->getLevel1TaxTotal();
                    $tax2 += $taxCalculator->getLevel2TaxTotal();
                    $taxSubtotal += $taxCalculator->getTotalBeforeTaxes();
                } else {
                    $taxSubtotal += $item->amount;
                }
            } else {
                $nonTaxSubtotal += $item->amount;
            }
        }

        if (!\WHMCS\Config\Setting::getValue("TaxPerLineItem")) {
            $taxCalculator->setTaxBase((float)$taxSubtotal);
            $tax = $taxCalculator->getLevel1TaxTotal();
            $tax2 = $taxCalculator->getLevel2TaxTotal();
            $taxSubtotal = $taxCalculator->getTotalBeforeTaxes();
        }
        $subtotal = $nonTaxSubtotal + $taxSubtotal;
        $total = $subtotal + $tax + $tax2;
        $this->subtotal = $subtotal;
        $this->tax = $tax;
        $this->tax2 = $tax2;
        $this->total = $total;
        $this->save();
    }

    function getType()
    {
        return CreditLineOperationLogger::RESELLER_INVOICE_TYPE;
    }

    function getReseller()
    {
        return $this->reseller;
    }

    public function decrementNumbering()
    {
        $reseller = new Reseller($this->reseller);
        if ($reseller->exists) {
            RcInvoiceNumberingHelper::decrementNextInvoiceNumber($reseller);
        }
    }

    public function setCustomInvoiceNumber()
    {
        $reseller = new Reseller($this->reseller);
        $this->invoicenum = $reseller->settings->getNextInvoiceNumber();
    }
}
