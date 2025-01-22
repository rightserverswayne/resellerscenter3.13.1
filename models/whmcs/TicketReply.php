<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

use MGModule\ResellersCenter\core\helpers\Helper;
use Michelf\Markdown;
/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 * @var
 */
class TicketReply extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblticketreplies';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array();

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
     * Get realted client
     */
    public function client()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Client", "userid");
    }
    
    public function getMarkdownMessage()
    {
        $this->message = Helper::ticketMessageFormat($this->message);
        $this->message = preg_replace("/<br \/>/", "  ", html_entity_decode($this->message));
        return Markdown::defaultTransform($this->message);
    }
}
