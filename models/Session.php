<?php
namespace MGModule\ResellersCenter\models;
use \Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Description of Reseller
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Session extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_SessionStorage';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array();

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('key', 'time', 'session');

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
 
    //Disable Updated At
    public function setUpdatedAt($value) {
        // Do nothing.
    }
}
