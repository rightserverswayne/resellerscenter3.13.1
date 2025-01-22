<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of TldCategory
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class TldCategory extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tbltld_categories';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('category', 'is_primary', 'display_order');

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
