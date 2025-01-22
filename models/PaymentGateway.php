<?php
namespace MGModule\ResellersCenter\models;

use \Illuminate\Database\Eloquent\Model as EloquentModel;
/**
 * Description of Reseller
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class PaymentGateway extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_PaymentGateways';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array();

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('reseller_id', 'gateway', 'setting', 'value');

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
}