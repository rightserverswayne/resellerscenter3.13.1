<?php
namespace MGModule\ResellersCenter\models;
use \Illuminate\Database\Eloquent\model as EloquentModel;

use MGModule\ResellersCenter\models\source\ModelException;

/**
 * Model for Group Contents (products, addons, domains)
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ContentPricing extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_GroupsContentsPricing';

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('relid', 'type', 'currency', 'billingcycle', 'value');

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = array();
    
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

       
    public function findByKeys($cid, $type, $currency, $billingcycle)
    {
        if(!is_numeric($cid)) {
            throw new ModelException("invalid_relid");
        }
        if(empty($currency)) {
            throw new ModelException("invalid_currency");
        }
        if(empty($type)) {
            throw new ModelException("invalid_pricing_type");
        }
        if(empty($billingcycle)) {
            throw new ModelException("invalid_pricing_type");
        }
        
        $result = $this->where('relid',$cid)
                    ->where('type',$type)
                    ->where('currency',$currency)
                    ->where('billingcycle',$billingcycle)
                    ->take(1)->get();

        if($result->isEmpty())
        {
            $result = null;
        }
        
        return $result;
    }
    
    public function setData($cid, $currency, $type, $billingcycle, $value)
    {
        if(!is_numeric($cid)) {
            throw new ModelException("invalid_relid");
        }
        if(empty($currency)) {
            throw new ModelException("invalid_currency");
        }
        if(empty($type)) {
            throw new ModelException("invalid_pricing_type");
        }
        
        $this->relid = $cid;
        $this->currency = $currency;
        $this->type = $type;
        $this->billingcycle = $billingcycle;
        $this->value = $value;
    }
            
    public function updateData($cid, $currency, $type, $billingcycle, $value)
    {
        if(!is_numeric($cid)) {
            throw new ModelException("invalid_relid");
        }
        if(empty($currency)) {
            throw new ModelException("invalid_currency");
        }
        if(empty($type)) {
            throw new ModelException("invalid_pricing_type");
        }
        
        $data = array();
        $data["relid"] = $cid;
        $data["currency"] = $currency;
        $data["type"] = $type;
        $data["billingcycle"] = $billingcycle;
        $data["value"] = $value;

        $this->where('relid',$cid)
            ->where('type',$type)
            ->where('currency',$currency)
            ->where('billingcycle',$billingcycle)
            ->update($data);
    }
}
