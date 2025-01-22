<?php
namespace MGModule\ResellersCenter\models;

use MGModule\ResellersCenter\models\source\ModelException;
use \Illuminate\Database\Eloquent\Model as EloquentModel;
/**
 * Description of Reseller
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ResellerClient extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_ResellersClients';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('client_id', 'reseller_id');

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
    public $timestamps = true;
    
    
    public function fillData($cid, $rid)
    {
        if(empty($cid)){
            throw new ModelException("invalid_clientid");
        }
        
        if(empty($rid)){
            throw new ModelException("invalid_resellerid");
        }

        $this->client_id = $cid;
        $this->reseller_id = $rid;
    }
    
    public function reseller()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\Reseller", "reseller_id");
    }
    
    public function whmcsClient()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Client", "id", "client_id");
    }

    public function settings()
    {
        return $this->hasMany(ResellersClientsSetting::class, "reseller_client_id");
    }
}
