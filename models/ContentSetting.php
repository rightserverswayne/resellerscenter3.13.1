<?php
namespace MGModule\ResellersCenter\models;
use \Illuminate\Database\Eloquent\model as EloquentModel;

use MGModule\ResellersCenter\models\source\ModelException;
use MGModule\ResellersCenter\models\source\Validator;

/**
 * Model for Group Contents (products, addons, domains)
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ContentSetting extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_GroupsContentsSettings';

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('relid', 'group_id', 'setting', 'value');

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    
    protected $dates = array('deleted_at', 'updated_at');
    /**
     * Indicates if the model should soft delete.
     *
     * @var bool
     */
    protected $softDelete = false;
    
    
    public function findByKeys($relid, $groupid, $setting)
    {
        if(Validator::isEmpty($setting))
        {
            throw new ModelException("invalid_content_setting");
        }
        
        if(!Validator::isNumber($groupid))
        {
            throw new ModelException("invalid_groupid");
        }

        if(!Validator::isNumber($relid))
        {
            throw new ModelException("invalid_relid");
        }
        
        $result = $this->where('relid',$relid)
                    ->where('group_id',$groupid)
                    ->where('setting',$setting)
                    ->take(1)->get();

        return $result;
    }
}