<?php
namespace MGModule\ResellersCenter\models;

use \Illuminate\Database\Eloquent\Model as EloquentModel;
/**
 * Description of Reseller
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Transaction extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_Transactions';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('userid', 'currency', 'gateway', 'date', 'description', 'amountin', 'fees', 'amountout', 'rate', 'transid', 'invoice_id', 'refundid');

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = array('date');
    
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
    
    public function getAmountAttribute()
    {
        return $this->attributes["amountin"] - $this->attributes["amountout"];
    }
}
