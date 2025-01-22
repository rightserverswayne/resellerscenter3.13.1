<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of ClientGroup
 * 
 * @var id
 * @var groupname
 * @var groupcolour
 * @var discountpercent
 * @var susptermexempt
 * @var separateinvoices
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ClientGroup extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblclientgroups';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('groupname', 'groupcolour', 'discountpercent', 'susptermexempt', 'separateinvoices');

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
     * Get client related hostings
     * 
     * @return type
     */
    public function clients()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\whmcs\Clients", 'groupid');
    }
}
