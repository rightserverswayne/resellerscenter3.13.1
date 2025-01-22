<?php
namespace MGModule\ResellersCenter\models;
use \Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Description of Reseller
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class EmailTemplate extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_EmailTemplates';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('reseller_id', 'relid', 'subject', 'message', 'language');

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
    
    
    /**
     * Reseller relationship
     * 
     * @return type
     */
    public function reseller()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\Reseller");
    }
}
