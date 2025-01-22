<?php
namespace MGModule\ResellersCenter\models;
use \Illuminate\Database\Eloquent\model as EloquentModel;

use MGModule\ResellersCenter\repository\whmcs\Pricing;
use MGModule\ResellersCenter\models\source\ModelException;

use MGModule\ResellersCenter\repository\Contents;

/**
 * Model for Group Contents (products, addons, domains)
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ResellerPricing extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_ResellersPricing';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');
    
    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('reseller_id', 'type', 'relid', 'currency', 'billingcycle', 'value');

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
    
    public function findByKeys($resellerid, $relid, $currency, $billingcycle)
    {
        $result = $this->where('reseller_id',$resellerid)
                    ->where('relid',$relid)
                    ->where('currency',$currency)
                    ->where('billingcycle',$billingcycle)
                    ->take(1)->get();

        return $result;
    }

    public function setData($resellerid, $relid, $type, $currency, $billingcycle, $value)
    {
        $this->checkProvidedData($resellerid, $relid, $type, $currency, $billingcycle);

        $this->reseller_id  = $resellerid;
        $this->relid        = $relid;
        $this->type         = $type;
        $this->currency     = $currency;
        $this->billingcycle = $billingcycle;
        $this->value        = $value;
    }
            
    public function updateData($resellerid, $contentid, $type, $currency, $billingcycle, $value)
    {
        $this->checkProvidedData($resellerid, $contentid, $type, $currency, $billingcycle);
        
        $data = array();
        $data["reseller_id"] = $resellerid;
        $data["relid"] = $contentid;
        $data["type"] = $type;
        $data["currency"] = $currency;
        $data["billingcycle"] = $billingcycle;
        $data["value"] = $value;

        $this->where('relid',$contentid)
            ->where('reseller_id',$resellerid)
            ->where('type',$type)
            ->where('currency',$currency)
            ->where('billingcycle',$billingcycle)
            ->update($data);
    }

    /**
     * @param $query
     * @param $relid
     * @return mixed
     */
    public function scopeByRelid($query, $relid)
    {
        $query->where("relid", $relid);
        return $query;
    }

    public function scopeByType($query, $type)
    {
        $query->where("type", $type);
        return $query;
    }
    
    private function checkProvidedData($resellerid, $contentid, $type, $currency, $billingcycle)
    {
        if(!is_numeric($resellerid)) {
            throw new ModelException("invalid_resellerid");
        }
        if(!is_numeric($currency)) {
            throw new ModelException("invalid_currency");
        }
        if(!is_numeric($contentid)) {
            throw new ModelException("invalid_contentid");
        }
        if(!in_array($billingcycle, Pricing::BILLING_CYCLES) && !in_array($billingcycle, Pricing::DOMAIN_PEROIDS) && !in_array($billingcycle, Pricing::SETUP_FEES)) {
            throw new ModelException("invalid_billingcycle");
        }
        if(!in_array($type, Contents::TYPES)) {
            throw new ModelException("invalid_type");
        }
    }
}
