<?php
namespace MGModule\ResellersCenter\models\whmcs;

use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of Cancel Request
 * 
 * @var int id
 * @var datetime date
 * @var int relid
 * @var string reason
 * @var string type
 * @var timestamp created_at
 * @var timestamp updated_at
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class CancelRequest extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblcancelrequests';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('date', 'relid', 'reason', 'type', 'created_at', 'updated_at');

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
