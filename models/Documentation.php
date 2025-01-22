<?php
namespace MGModule\ResellersCenter\models;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of Documentation
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Documentation extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_Documentations';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('name', 'content', 'pdfpath', 'created_at', 'updated_at');

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
}
