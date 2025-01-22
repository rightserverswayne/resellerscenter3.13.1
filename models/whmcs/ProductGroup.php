<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;


/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ProductGroup extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblproductgroups';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('id', 'name', 'headline', 'tagline', 'orderfrmtpl', 'disabledgateways', 'hidden', 'order', 'created_at', 'updated_at');
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = array('updated_at', 'created_at');

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
    
    /**
     * Add relation to products
     */
    public function products()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\whmcs\Product", "gid");
    }
}
