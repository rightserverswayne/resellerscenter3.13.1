<?php
namespace MGModule\ResellersCenter\models;
use \Illuminate\Database\Eloquent\model as EloquentModel;

use MGModule\ResellersCenter\models\source\ModelException;
use MGModule\ResellersCenter\models\source\Validator;

use MGModule\ResellersCenter\repository\Contents;
use MGModule\ResellersCenter\repository\ContentsSettings;
/**
 * Model for Group Contents (products, addons, domains)
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Content extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_GroupsContents';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('group_id', 'relid', 'type');

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
    
    public function product()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Product", "id", "relid");
    }
    
    public function addon()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Addon", "id", "relid");
    }
    
    public function domain()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\DomainPricing", "id", "relid");
    }
    
    /**
     * Validate and set data for group content
     * 
     * @since 3.0.0
     * @author Paweł Złamaniec <pawel.zl@modulesgaren.com>
     * @param int groupid
     * @param int relid
     * @param string type
     * @throws ModelException
     */
    public function setData($groupid, $relid, $type)
    {
        if(Validator::isEmpty($type) || !in_array($type, Contents::TYPES))
        {
            throw new ModelException("invalid_content_type");
        }
        
        if(!Validator::isNumber($groupid))
        {
            throw new ModelException("invalid_groupid");
        }

        if(!Validator::isNumber($relid))
        {
            throw new ModelException("invalid_relid");
        }

        $this->group_id = $groupid;
        $this->relid = $relid;
        $this->type = $type;
    }
    
    public function getConfig()
    {
        $repo = new ContentsSettings();
        $config = $repo->getConfigByContentId($this->id);
        
        return $config;
    }
}
