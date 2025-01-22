<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of Credit
 * 
 * @var int id
 * @var int clientid
 * @var date date
 * @var string description
 * @var string amount
 * @var string relid
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Credit extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblcredit';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('clientid', 'date', 'description', 'amount', 'relid');

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
}
