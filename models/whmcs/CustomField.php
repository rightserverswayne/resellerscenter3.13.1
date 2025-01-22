<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

use MGModule\ResellersCenter\models\whmcs\CustomFieldValue;
/**
 * Description of CustomField
 * 
 * @var id
 * @var type
 * @var relid
 * @var fieldname
 * @var fieldtype
 * @var description
 * @var fieldoptions
 * @var regexpr
 * @var adminonly
 * @var required
 * @var showorder
 * @var showinvoice
 * @var sortorder
 * @var created_at
 * @var updated_at
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class CustomField extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblcustomfields';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('type', 'relid', 'fieldname', 'fieldtype', 'description', 'fieldoptions', 'regexpr', 'adminonly', 'required', 'showorder', 'showinvoice', 'sortorder');

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
    
    public function getValueModel($relid)
    {
        $field = new CustomFieldValue();
        $result = $field->where("fieldid", $this->attributes["id"])->where("relid", $relid)->first();
        
        return $result;
    }
    
    public function getValueByRelid($relid)
    {
        $result = $this->getValueModel($relid);
        return $result->value;
    }
}
