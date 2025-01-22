<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

use MGModule\ResellersCenter\core\helpers\Helper;
use Michelf\Markdown;
/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Ticket extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tbltickets';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('tid', 'did', 'userid', 'contactid', 'name', 'email', 'cc', 'c', 'date', 'title', 'message', 'status', 'urgency', 'admin', 'attachment', 'lastreply', 'flag', 'clientunread', 'adminunread', 'replyingadmin', 'replyingtime', 'service', 'merged_ticket_id', 'editor');

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
     * Get Realted client
     */
    public function client()
    {
       return $this->belongsTo('MGModule\ResellersCenter\models\whmcs\Client','userid');
    }
    
    public function clientRC()
    {
        return $this->belongsTo('MGModule\ResellersCenter\models\ResellerClient', 'userid', "client_id");
    }
    
    public function replies()
    {
        return $this->hasMany('MGModule\ResellersCenter\models\whmcs\TicketReply','tid');
    }
        
    public function department()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\TicketDepartment", "did");
    }
    
    public function updateStatus($status)
    {
        $this->status = $status;
        $this->save();
    }
    
    public function getMarkdownMessage()
    {        
        $this->message = Helper::ticketMessageFormat($this->message);
        $this->message = preg_replace("/<br \/>/", "  ", html_entity_decode($this->message));
        return Markdown::defaultTransform($this->message);
    }
    
//    /**
//     * Get client related services
//     */
//    public function services()
//    {
//        return $this->hasManyThrough('MGModule\ResellersCenter\models\whmcs\Hosting', 'MGModule\ResellersCenter\models\whmcs\Client', "id", "userid");
//    }
}
