<?php
namespace MGModule\ResellersCenter\models;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of Promotion
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Promotion extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_Promotions';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('code', 'type', 'recurring', 'value', 'cycles', 'appliesto', 'requires', 'requiresexisting', 'startdate', 'expirationdate', 'maxuses', 'uses', 'lifetimepromo', 'applyonce', 'newsignups', 'existingclient', 'onceperclient', 'recurfor', 'upgrades', 'upgradeconfig', 'notes');

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = array('startdate', 'expirationdate');
    
    /**
     * Indicates if the model should soft delete.
     *
     * @var bool
     */
    protected $softDelete = true;
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
