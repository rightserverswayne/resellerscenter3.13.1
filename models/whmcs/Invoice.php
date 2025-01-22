<?php

namespace MGModule\ResellersCenter\models\whmcs;

use MGModule\ResellersCenter\Core\Helpers\InvoiceNumberingHelper;
use MGModule\ResellersCenter\Core\Whmcs\Services\AbstractService;
use MGModule\ResellersCenter\libs\CreditLine\Helpers\CreditLineOperationLogger;
use MGModule\ResellersCenter\libs\CreditLine\Interfaces\InvoiceModelInterface;
use MGModule\ResellersCenter\models\Reseller;
use MGModule\ResellersCenter\repository\whmcs\Invoices;
use MGModule\ResellersCenter\Repository\Source\AbstractRepository;
use \WHMCS\Billing\Invoice as WhmcsInvoiceModel;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;

/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Invoice extends WhmcsInvoiceModel implements InvoiceModelInterface
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblinvoices';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('userid', 'invoicenum', 'date', 'duedate', 'datepaid', 'subtotal', 'credit', 'tax', 'tax2', 'total', 'taxrate', 'taxrate2', 'status', 'paymentmethod', 'notes');

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
    
    /**
     * Add relation to invoice items
     * 
     * @return type
     */
    public function items()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\whmcs\InvoiceItem", "invoiceid");
    }
    
    public function transactions()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\whmcs\Transaction", "invoiceid");
    }
    
    public function client()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Client", "userid");
    }
    
    public function order()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Order", "invoiceid");
    }
    
    public function snaphot()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\InvoiceData", "id", "invoiceid");
    }
    
    public function branded()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\BrandedInvoice", "invoice_id");
    }
    
    public function resellerInvoice()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\Invoice", "id", "relinvoice_id");
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

    public function recalculate()
    {
        if (!function_exists("updateInvoiceTotal")) {
            require_once ROOTDIR.DS.'includes'.DS.'invoicefunctions.php';
        }
        updateInvoiceTotal($this->id);
    }

    function getType()
    {
        return CreditLineOperationLogger::WHMCS_INVOICE_TYPE;
    }

    public function getOrderIdFromItems()
    {
        foreach ($this->items as $item) {
            $service = $item->getServiceAttribute();

            if ($service && is_subclass_of($service, AbstractService::class)) {
                return $service->getOrderId();
            }
        }
        return false;
    }

    function getReseller()
    {
        return ResellerHelper::isReseller($this->userid) ? Reseller::where('client_id', $this->userid)->first() : $this->client->resellerClient->reseller;
    }

    public function decrementNumbering()
    {
        if (\WHMCS\Config\Setting::getValue("TaxCustomInvoiceNumbering") && $this->status == Invoices::STATUS_UNPAID) {
            InvoiceNumberingHelper::decrementTaxCustomInvoiceNumber();
        }
    }

    public function setCustomInvoiceNumber()
    {
        if (\WHMCS\Config\Setting::getValue("TaxCustomInvoiceNumbering")) {
            $this->invoiceNumber = '';
            $this->vat()->setCustomInvoiceNumberFormat();
        }
    }
}
