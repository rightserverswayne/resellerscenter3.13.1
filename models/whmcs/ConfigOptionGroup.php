<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of ConfigOptionGroup
 * 
 * @var id
 * @var name
 * @var description
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ConfigOptionGroup extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblproductconfiggroups';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('name', 'description');

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
    
    public function options()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\whmcs\ConfigOption", "gid");
    }
}
