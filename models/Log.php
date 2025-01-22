<?php
namespace MGModule\ResellersCenter\models;
use \Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Description of Reseller
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Log extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_Logs';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('reseller_id', 'client_id', 'description');

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = array('created_at');
    
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
    
    
    /**
     * Reseller relationship
     * 
     * @return type
     */
    public function reseller()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\Reseller");
    }
    
    /**
     * Client realtionship
     * 
     * @return type
     */
    public function client()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Client");
    }
    
    /**
     * Disable UpdateAt function
     * 
     * @param type $value
     */
    public function setUpdatedAt($value) {
        // Do nothing.
    }
    
}
