<?php
namespace MGModule\ResellersCenter\models\whmcs;

use MGModule\ResellersCenter\repository\whmcs\Pricing as WHMCSPricing;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of Addon
 * 
 * @var int id
 * @var string packages
 * @var string name
 * @var string description
 * @var string billingcycle
 * @var string tax
 * @var string showorder
 * @var string downloads
 * @var string autoactivate
 * @var string suspendproduct
 * @var int welcomeemail
 * @var int weight
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Addon extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tbladdons';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('packages', 'name', 'description', 'billingcycle', 'tax', 'showorder', 'downloads', 'autoactivate', 'suspendproduct', 'welcomeemail', 'weight');

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
    
   
    public function getBillingcycleAttribute()
    {
        if($this->attributes['billingcycle'] == "recurring" || $this->attributes['billingcycle'] == "onetime" || $this->attributes['billingcycle'] == "free") {
            return $this->attributes['billingcycle'];
        }
        
        return WHMCSPricing::BILLING_CYCLES[$this->attributes['billingcycle']];
    }
    
    public function getBillingcycleFriendlyAttribute()
    {
         return $this->attributes['billingcycle'];
    }
    
    public function getPricingColumnAttribute()
    {
        return WHMCSPricing::BILLING_CYCLES[$this->attributes['billingcycle']];
    }
    
    public function getPricing($currencyid = null)
    {
        $pricing = new WHMCSPricing();
        $result = $pricing->getPricingByRelIdAndType($this->attributes['id'], WHMCSPricing::TYPE_ADDON, $currencyid);
        
        return $result;
    }
}
