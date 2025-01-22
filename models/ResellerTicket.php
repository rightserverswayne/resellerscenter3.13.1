<?php
namespace MGModule\ResellersCenter\models;
use \Illuminate\Database\Eloquent\Model as EloquentModel;
use MGModule\ResellersCenter\models\source\ModelException;

/**
 * Description of Settings
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ResellerTicket extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_ResellersTickets';

    /**
     * @var array
     */
    protected $guarded = array();
    
    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('ticket_id', 'reseller_id');

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
    
    /**
     * Add relation with Reseller
     * 
     * @return type
     */
    public function reseller()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\Reseller", "reseller_id");
    }
    
    /**
     * Add relation with whmcs ticket
     * 
     * @return type
     */
    public function ticket()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Ticket", "ticket_id");
    }
}
