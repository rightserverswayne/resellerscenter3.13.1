<?php
namespace MGModule\ResellersCenter\models\whmcs;

use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of TransientData
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class TransientData extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tbltransientdata';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('name', 'data', 'expires');

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
