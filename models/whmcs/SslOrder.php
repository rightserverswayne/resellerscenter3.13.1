<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of SslOrder
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class SslOrder extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblsslorders';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = ["userid", "serviceid", "addon_id", "remoteid", "module", "certtype", "configdata", "completiondate", "status"];

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

    public function hosting()
    {
        return $this->belongsTo('MGModule\ResellersCenter\models\whmcs\Hosting', 'serviceid');
    }

    
    public function addon()
    {
        return $this->belongsTo('MGModule\ResellersCenter\models\whmcs\HostingAddon', 'addon_id');
    }

    /**
     * Get related client
     */
    public function client()
    {
       return $this->belongsTo('MGModule\ResellersCenter\models\whmcs\Client','userid');
    }
    
    /**
     * Get Related RC client
     */
    public function clientRC()
    {
        return $this->belongsTo('MGModule\ResellersCenter\models\ResellerClient', 'userid', "client_id");
    }
}
