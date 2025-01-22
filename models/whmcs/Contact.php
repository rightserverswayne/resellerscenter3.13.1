<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of Contact
 * 
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Contact extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblcontacts';

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

    public function scopeByEmail($query, $email)
    {
        $query->where("email", $email);
    }
}