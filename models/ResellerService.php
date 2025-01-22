<?php
namespace MGModule\ResellersCenter\models;
use \Illuminate\Database\Eloquent\Model as EloquentModel;
use MGModule\ResellersCenter\models\source\ModelException;

use MGModule\ResellersCenter\repository\ResellersServices;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;

/**
 * Description of Settings
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ResellerService extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_ResellersServices';

    /**
     * @var array
     */
    protected $guarded = array('id');
    
    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('reseller_id', 'type', 'relid');

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = array("created_at", "updated_at");
    
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
    public $timestamps = true;
    
    public function fillData($rid, $relid, $type)
    {
        if(empty($rid) || !is_numeric($rid)){
            throw new ModelException("invalid_resellerid");
        }
        
        if(empty($relid) || !is_numeric($relid)){
            throw new ModelException("invalid_clientid");
        }   
        
        if(!in_array($type, ResellersServices::TYPES)){
            throw new ModelException("invalid_type");
        }   
        
        $this->reseller_id = $rid;
        $this->relid = $relid;
        $this->type = $type;
    }
    
    public function hosting()
    {
        if ($this->type == ResellersServices::TYPE_HOSTING) {
            return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Hosting", "id", "relid");
        } else {
            throw new ModelException("Item is not Hosting type");
        }
    }
    
    public function addon()
    {
        if ($this->type == ResellersServices::TYPE_ADDON) {
            return $this->hasOne("MGModule\ResellersCenter\models\whmcs\HostingAddon", "id", "relid");
        } else {
            throw new ModelException("Item is not Addon type");
        }
    }
    
    public function domain()
    {
        if ($this->type == ResellersServices::TYPE_DOMAIN) {
            return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Domain", "id", "relid");
        } else {
            throw new ModelException("Item is not Domain type");
        }
    }
    
    public function getInvoiceAttribute()
    {
        $repo = new InvoiceItems();
        if($this->type == ResellersServices::TYPE_HOSTING)
        {
            $types = [InvoiceItems::TYPE_HOSTING, InvoiceItems::TYPE_ABHOSTING, InvoiceItems::TYPE_ABHOSTING_ITEM];
            $invoiceItem = $repo->where("relid", $this->relid)->whereIn("type", $types)->first();
        }
        elseif($this->type == ResellersServices::TYPE_ADDON)
        {
            $invoiceItem = $repo->where("relid", $this->relid)->where("type", InvoiceItems::TYPE_ADDON)->first();
        }
        elseif($this->type == ResellersServices::TYPE_DOMAIN)
        {
            $type = "Domain" . $this->domain->type;
            $invoiceItem = $repo->where("relid", $this->relid)->where("type", $type)->first();
        }
        
        return $invoiceItem->invoice;
    }
    
    public function getServiceAttribute()
    {
        if($this->type == ResellersServices::TYPE_HOSTING)
        {
            return $this->hosting;
        }
        elseif($this->type == ResellersServices::TYPE_ADDON)
        {
            return $this->addon;
        }
        elseif($this->type == ResellersServices::TYPE_DOMAIN)
        {
            return $this->domain;
        }
    }
    
    public function reseller()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\Reseller", "reseller_id");
    }
}
