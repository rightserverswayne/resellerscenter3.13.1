<?php
namespace MGModule\ResellersCenter\models;

use \Illuminate\Database\Eloquent\Model as EloquentModel;
/**
 * Description of Reseller
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class PaymentGatewayLog extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_PaymentGatewaysLogs';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array();

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('date','gateway', 'data', 'result');

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