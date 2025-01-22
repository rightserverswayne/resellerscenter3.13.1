<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of ConfigOptionGroup
 * 
 * @var id
 * @var configid
 * @var optionname
 * @var sortorder
 * @var hidden
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ConfigOptionSub extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblproductconfigoptionssub';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('configid', 'optionname', 'sortorder', 'hidden');

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

    public function configOption()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\ConfigOption", "configid");
    }
}
