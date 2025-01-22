<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;


/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Upgrade extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblupgrades';

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
    
    public function order()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Order", "orderid");
    }
    
    public function hosting()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Hosting", "relid");
    }
    
    public function productFrom()
    {
        if($this->type != 'package')  
        {
            return new \stdClass();
        }
        
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Product", "originalvalue");
    }
    
    public function getProductNewAttribute()
    {
        if($this->type != 'package')
        {
            return new \stdClass();
        }
        
        $newvalue = explode(",", $this->newvalue);
        $newProductId = $newvalue[0];
        
        $repo = new \MGModule\ResellersCenter\repository\whmcs\Products();
        $product = $repo->find($newProductId);
        
        return $product;
    }
    
    public function getNewBillingcycleAttribute()
    {
        $newvalue = explode(",", $this->newvalue);
        return $newvalue[1];
    }
}