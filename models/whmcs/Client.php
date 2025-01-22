<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

use MGModule\ResellersCenter\models\CreditLine;
use MGModule\ResellersCenter\repository\whmcs\Taxes;
use MGModule\ResellersCenter\repository\whmcs\CustomFields;

/**
 * Description of Client
 * 
 * @var int id
 * @var string uuid
 * @var string firstname
 * @var string lastname
 * @var string companyname
 * @var string email
 * @var string address1
 * @var string address2
 * @var string city
 * @var string state
 * @var string postcode
 * @var string country
 * @var string phonenumber
 * @var string password
 * @var string authmodule
 * @var string authdata
 * @var int currency
 * @var string defaultgateway
 * @var double credit
 * @var int taxexempt
 * @var int latefeeoveride
 * @var int overideduenotices
 * @var int separateinvoices
 * @var int disableautocc
 * @var datetime datecreated
 * @var string notes
 * @var int billingcid
 * @var int securityqid
 * @var string securityqans
 * @var int groupid
 * @var text cardtype
 * @var text cardlastfour
 * @var string cardnum
 * @var string startdate
 * @var string expdate
 * @var string issuenumber
 * @var string bankname
 * @var string banktype
 * @var string bankcode
 * @var string bankacct
 * @var string gatewayid
 * @var datetime lastlogin
 * @var string ip
 * @var string host
 * @var enum('Active', 'Inactive', 'Closed') status
 * @var string language
 * @var string pwresetkey
 * @var int emailoptout
 * @var int overrideautoclose
 * @var int allow_sso
 * @var int email_verified
 * @var timestamp created_at
 * @var timestamp updated_at
 * @var timestamp pwresetexpiry
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Client extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblclients';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('uuid', 'firstname', 'lastname', 'companyname', 'email', 'address1', 'address2', 'city', 'state', 'postcode', 'country', 'phonenumber', 'password', 'authmodule', 'authdata', 'currency', 'defaultgateway', 'credit', 'taxexempt', 'latefeeoveride', 'overideduenotices', 'separateinvoices', 'disableautocc', 'datecreated', 'notes', 'billingcid', 'securityqid', 'securityqans', 'groupid', 'cardtype', 'cardlastfour', 'bankname', 'banktype', 'gatewayid', 'lastlogin', 'ip', 'host', 'status', 'language', 'pwresetkey', 'emailoptout', 'overrideautoclose', 'allow_sso', 'email_verified', 'created_at', 'updated_at', 'pwresetexpiry');

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
    
    public function contacts()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\whmcs\Contact", 'userid');
    }
    
    public function hostings()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\whmcs\Hosting", 'userid');
    }

    public function domains()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\whmcs\Domain", 'userid');
    }

    public function hostingAddons()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\whmcs\HostingAddon", 'userid');
    }

    public function currencyObj()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Currency", 'id', 'currency');
    }
    
    public function orders()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\whmcs\Order", "userid");
    }
    
    public function transactions()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\whmcs\Transaction", "userid");
    }
    
    public function group()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\ClientGroup", "groupid");
    }
    
    public function invoices()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\whmcs\Invoice", "userid");
    }
    
    public function resellerInvoices()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\Invoice", "userid");
    }
    
    public function resellerClient()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\ResellerClient");
    }

    public function payMethods()
    {
        return $this->hasMany("WHMCS\\Payment\\PayMethod\\Model", "userid");
    }

    public function creditLine()
    {
        return $this->hasOne(CreditLine::class);
    }

    public function getFirstnameAttribute()
    {
        return preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, html_entity_decode($this->attributes["firstname"]));
    }
    
    public function getLastnameAttribute()
    {
        return preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, html_entity_decode($this->attributes["lastname"])); 
    }
    
    public function getTaxAttribute()
    {
        $repo = new Taxes();
        return $repo->getTax(1, $this->attributes["state"], $this->attributes["country"]);
    }
    
    public function getTax2Attribute()
    {
        $repo = new Taxes();
        return $repo->getTax(2, $this->attributes["state"], $this->attributes["country"]);
    }
    
    public function getCustomFieldsAttribute()
    {
        $repo = new CustomFields();
        return $repo->getClientFields(true);
    }

    public function scopeByEmail($query, $email)
    {
        $query->where("email", $email);
    }
}
