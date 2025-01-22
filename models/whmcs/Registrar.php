<?php
namespace MGModule\ResellersCenter\Models\Whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of Registrar
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Registrar extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblregistrars';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array();

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
