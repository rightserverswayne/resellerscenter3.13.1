<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;

/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Order extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblorders';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('ordernum', 'userid', 'contactid', 'date', 'nameservers', 'transfersecret', 'renewals', 'promocode', 'promotype', 'promovalue', 'orderdata', 'amount', 'paymentmethod', 'invoiceid', 'status', 'ipaddress', 'fraudmodule', 'fraudoutput', 'notes');

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
    
    
    public function hostings()
    {
        return $this->hasMany('MGModule\ResellersCenter\models\whmcs\Hosting', 'orderid');
    }
    
    public function domains()
    {
        return $this->hasMany('MGModule\ResellersCenter\models\whmcs\Domain', 'orderid');
    }
    
    public function addons()
    {
        return $this->hasMany('MGModule\ResellersCenter\models\whmcs\HostingAddon', 'orderid');
    }
    
    public function upgrades()
    {
        return $this->hasMany('MGModule\ResellersCenter\models\whmcs\Upgrade', 'orderid');
    }
    
    public function invoice()
    {
        return $this->hasOne('MGModule\ResellersCenter\models\whmcs\Invoice', 'id', 'invoiceid');
    }
    
    /**
     * Get Realted client
     */
    public function client()
    {
       return $this->belongsTo('MGModule\ResellersCenter\models\whmcs\Client','userid');
    }
    
    /**
     * Get Related RC client
     */
    public function clientRC()
    {
        return $this->belongsTo('MGModule\ResellersCenter\models\ResellerClient', 'userid', "client_id");
    }
    
    public function getResellerInvoice()
    {
        $repo = new InvoiceItems();
        $item = $repo->getItemByRelidAndType($this->id, "RCOrder");
        
        return $item->invoice;
    }
}
