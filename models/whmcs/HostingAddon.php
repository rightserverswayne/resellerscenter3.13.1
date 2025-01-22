<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

use MGModule\ResellersCenter\repository\ResellersServices;
use MGModule\ResellersCenter\repository\whmcs\Pricing as WHMCSPricing;

/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class HostingAddon extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblhostingaddons';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array();

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
    
    public function addon()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Addon", "addonid");
    }
    
    public function hosting()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Hosting", "hostingid");
    }
    
    public function order()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Order", "orderid");
    }
    
    public function getBillingcycleAttribute()
    {
        $billingcycle = WHMCSPricing::BILLING_CYCLES[$this->attributes['billingcycle']];
        if(empty($billingcycle)) {
            return $this->attributes['billingcycle'];
        }
        
        return $billingcycle;
    }
    
    public function getBillingcycleFriendlyAttribute()
    {
         return $this->attributes['billingcycle'];
    }
    
    public function getResellerServiceAttribute()
    {
        $services = new ResellersServices();
        $service = $services->getByTypeAndRelId(ResellersServices::TYPE_ADDON, $this->attributes["id"]);
        
        return $service;
    }
}
