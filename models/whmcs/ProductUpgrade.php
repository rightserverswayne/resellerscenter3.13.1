<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;


/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ProductUpgrade extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblproduct_upgrade_products';

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
    
    public function product()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Product", "product_id");
    }
    
    public function newproduct()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Product", "upgrade_product_id");
    }
}