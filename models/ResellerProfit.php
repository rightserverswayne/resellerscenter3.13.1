<?php
namespace MGModule\ResellersCenter\models;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Model for Group Contents (products, addons, domains)
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ResellerProfit extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_ResellersProfits';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');
    
    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('reseller_id', 'invoiceitems_id', 'amount', 'collected');

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = array('created_at', 'updated_at');
    
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
    

    public function reseller()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\Reseller");
    }
    
    public function invoiceitem()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\InvoiceItem", "id", "invoiceitem_id");
    }
}
