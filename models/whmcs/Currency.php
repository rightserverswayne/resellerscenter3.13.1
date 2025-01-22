<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of Currency
 * 
 * @var id
 * @var code
 * @var prefix
 * @var suffix
 * @var format
 * @var rate
 * @var default
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Currency extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblcurrencies';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('code', 'prefix', 'suffix', 'format', 'rate', 'default');

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
