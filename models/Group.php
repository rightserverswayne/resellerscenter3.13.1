<?php
namespace MGModule\ResellersCenter\models;
use \Illuminate\Database\Eloquent\model as EloquentModel;

use MGModule\ResellersCenter\models\source\ModelException;
use MGModule\ResellersCenter\models\source\Validator;

/**
 * Description of Group
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Group extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_Groups';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('name');

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = array('deleted_at', 'updated_at', 'created_at');
    
    /**
     * Indicates if the model should soft delete.
     *
     * @var bool
     */
    protected $softDelete = true;
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
    
    /**
     * Add relation with resellers
     * 
     * @return type
     */
    public function resellers()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\Reseller");
    }

    public function settings()
    {
        return $this->hasMany(GroupSetting::class, "group_id");
    }
    
    /**
     * Validate and set name for group
     * 
     * @since 3.0.0
     * @author Paweł Złamaniec <pawel.zl@modulesgaren.com>
     * @param string $name
     * @throws ModelException
     */
    public function setName($name)
    {
        if(!Validator::isEmpty($name))
        {
            $this->name = $name;
        }
        else
        {
            throw new ModelException("empty_name");
        }
    }
}
