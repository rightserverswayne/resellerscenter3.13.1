<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;


/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Email extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblemails';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array("userid", "subject", "message", "date", "to", "cc", "bcc", "attachments");

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
     * Add relation to client
     * 
     * @return type
     */
    public function client()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Client", "userid");
    }
}
