<?php
namespace MGModule\ResellersCenter\models\whmcs;

use MGModule\ResellersCenter\repository\ResellersServices;
use MGModule\ResellersCenter\repository\whmcs\CustomFields;
use MGModule\ResellersCenter\repository\whmcs\Pricing as WHMCSPricing;
use \Illuminate\Database\Eloquent\model as EloquentModel;


/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Hosting extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblhosting';

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
    
    /**
     * Get related product
     * 
     * @return type
     */
    public function product()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Product", "packageid");
    }
        
    public function addons()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\whmcs\HostingAddon", "hostingid");
    }
    
    public function client()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Client", "userid");
    }
    
    public function configOptions()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\whmcs\HostingConfigOption", "relid");
    }
    
    public function serverObj()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Server", "server");
    }
    
    public function order()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Order", "orderid");
    }

    public function sslorder()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\SslOrder", "serviceid");
    }
    
    public function cancelation()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\CancelRequest", "relid");
    }
    
    public function getBillingcycleAttribute()
    {
        return WHMCSPricing::BILLING_CYCLES[$this->attributes['billingcycle']];
    }
    
    public function getBillingcycleFriendlyAttribute()
    {
        return $this->attributes['billingcycle'];
    }
    
    public function getCustomFieldsAttribute()
    {
        $repo = new CustomFields();
        $fields = $repo->getProductFields();
        
        return $fields;
    }
    
    public function getResellerServiceAttribute()
    {
        $services = new ResellersServices();
        $service = $services->getByTypeAndRelId(ResellersServices::TYPE_HOSTING, $this->attributes["id"]);

        return $service;
    }

    public function getPrevduedateAttribute()
    {
        $year = substr($this->attributes["nextduedate"], 0, 4);
        $month = substr($this->attributes["nextduedate"], 5, 2);
        $day = substr($this->attributes["nextduedate"], 8, 2);

        $oldCycleMonths = getBillingCycleMonths(WHMCSPricing::BILLING_CYCLES[$this->attributes['billingcycle']]);
        return date("Y-m-d", mktime(0, 0, 0, $month - $oldCycleMonths, $day, $year));
    }
}
