<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of Configuration
 * 
 * @var setting
 * @var value
 * @var created_at
 * @var updated_at
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Configuration extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblconfiguration';

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('setting', 'value', 'created_at', 'updated_at');

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
