<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of TldCategoryPivot
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class TldCategoryPivot extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tbltld_category_pivot';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('tld_id', 'category_id');

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
    
    public function tld()
    {
        return $this->hasOne('MGModule\ResellersCenter\models\whmcs\Tld', 'tld_id');
    }
    
    public function category()
    {
        return $this->hasOne('MGModule\ResellersCenter\models\whmcs\TldCategory', 'tld_id');
    }
}
