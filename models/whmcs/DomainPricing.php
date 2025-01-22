<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of Domain Pricing
 * 
 * @var id
 * @var extension
 * @var dnsmanagement
 * @var emailforwarding
 * @var idprotection
 * @var eppcode
 * @var autoreg
 * @var order
 * @var group
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class DomainPricing extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tbldomainpricing';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('id', 'extension', 'dnsmanagement', 'emailforwarding', 'idprotection', 'eppcode', 'autoreg', 'order', 'group');

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
    
    public function getTldAttribute()
    {
        $repo = new \MGModule\ResellersCenter\repository\whmcs\Tlds();
        return $repo->getByTld($this->extensionNoDot);
    }
    
    public function getExtensionNoDotAttribute()
    {
        return substr($this->extension, 1);
    }
}
