<?php

namespace MGModule\ResellersCenter\models;

use MGModule\ResellersCenter\libs\CreditLine\Interfaces\InvoiceItemModelInterface;
use MGModule\ResellersCenter\repository\InvoiceItems;
use MGModule\ResellersCenter\repository\whmcs\Hostings;
use MGModule\ResellersCenter\repository\whmcs\HostingAddons;
use MGModule\ResellersCenter\repository\whmcs\Domains;

use \Illuminate\Database\Eloquent\Model as EloquentModel;
/**
 * Description of Reseller
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class InvoiceItem extends EloquentModel implements InvoiceItemModelInterface
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_InvoiceItems';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('reseller_id', 'invoice_id', 'userid', 'type', 'relid', 'description', 'amount', 'taxed', 'duedate', 'paymentmethod', 'notes');

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = array('duedate');
    
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
    
    public function client()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Client", "id", "userid");
    }
    
    public function invoice()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\Invoice", "invoice_id");
    }
    
    public function hosting()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Hosting", "id", "relid");
    }
    
    public function hostingAddon()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\HostingAddon", "id", "relid");
    }
    
    public function domain()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Domain", "id", "relid");
    }
    
    public function upgrade()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Upgrade", "id", "relid");
    }
    
    public function getDescriptionAttribute()
    {
        return preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, html_entity_decode($this->attributes["description"]));
    }
    
    public function getServiceAttribute()
    {
        if($this->type == InvoiceItems::TYPE_HOSTING)
        {            
            $repo = new Hostings();
            $service = $repo->find($this->relid);
            
            return $service;
        }
        elseif($this->type == InvoiceItems::TYPE_ADDON)
        {
            $repo = new HostingAddons();
            $service = $repo->find($this->relid);
            
            return $service;
        }
        elseif($this->type == InvoiceItems::TYPE_DOMAIN_REGISTER || $this->type == InvoiceItems::TYPE_DOMAIN_RENEW || $this->type == InvoiceItems::TYPE_DOMAIN_TRANSFER)
        {
            $repo = new Domains();
            $service = $repo->find($this->relid);
            
            return $service;
        }
        
        return new \stdClass();
    }
}
