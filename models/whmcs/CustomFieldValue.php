<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;


/**
 * Description of CustomFieldValue
 * 
 * @var fieldid
 * @var relid
 * @var value
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class CustomFieldValue extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblcustomfieldsvalues';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array();

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('fieldid', 'relid', 'value');

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
    public $timestamps = true;
}
