<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of AddonModule
 * 
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class AddonModule extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tbladdonmodules';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array("module", "setting", "value");

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
