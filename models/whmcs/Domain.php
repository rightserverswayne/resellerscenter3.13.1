<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

use MGModule\ResellersCenter\repository\ResellersServices;
use MGModule\ResellersCenter\repository\whmcs\Pricing as PricingRepo;
use MGModule\ResellersCenter\repository\whmcs\DomainPricing as WHMCSDomainPricing;
use MGModule\ResellersCenter\core\helpers\DomainHelper;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;

/**
 * Description of Domain
 * 
 * @var id 	
 * @var userid 	
 * @var orderid 	
 * @var type 	
 * @var registrationdate 	
 * @var domain 	
 * @var firstpaymentamount 	
 * @var recurringamount 	
 * @var registrar 	
 * @var registrationperiod 	
 * @var expirydate 	
 * @var subscriptionid 	
 * @var promoid 	
 * @var status 	
 * @var nextduedate 	
 * @var nextinvoicedate 	
 * @var additionalnotes 	
 * @var paymentmethod 	
 * @var dnsmanagement 	
 * @var emailforwarding 	
 * @var idprotection 	
 * @var is_premium 	
 * @var donotrenew 	
 * @var reminders 	
 * @var synced 	
 * @var created_at 	
 * @var updated_at
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Domain extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tbldomains';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('userid', 'orderid', 'type', 'registrationdate', 'domain', 'firstpaymentamount', 'recurringamount', 'registrar', 'registrationperiod', 'expirydate', 'subscriptionid', 'promoid', 'status', 'nextduedate', 'nextinvoicedate', 'additionalnotes', 'paymentmethod', 'dnsmanagement', 'emailforwarding', 'idprotection', 'is_premium', 'donotrenew', 'reminders', 'synced', 'created_at', 'updated_at');

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
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Client", "userid");
    }
    
    public function order()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Order", "orderid");
    }
    
    public function getInvoiceitemAttribute()
    {
        $repo = new InvoiceItems();
        $item = $repo->getItemByRelidAndType($this->id, "Domain".$this->type);
        
        return $item;
    }
    
    public function getDomainPricingAttribute()
    {
        $domain = new DomainHelper($this->attributes["domain"]);
        $tld = $domain->getTLD();
        
        $repo = new WHMCSDomainPricing();
        $result = $repo->getByTld($tld);
        
        return $result;
    }
    
    /**
     * Get Pricing column
     */
    public function getBillingcycleAttribute()
    {
        return PricingRepo::DOMAIN_PEROIDS[$this->attributes['registrationperiod']];
    }
    
    public function getResellerServiceAttribute()
    {
        $services = new ResellersServices();
        $service = $services->getByTypeAndRelId(ResellersServices::TYPE_DOMAIN, $this->attributes["id"]);
        
        return $service;
    }
}
