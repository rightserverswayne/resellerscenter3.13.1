<?php
namespace MGModule\ResellersCenter\repository;

use MGModule\ResellersCenter\models\ContentSetting;
use MGModule\ResellersCenter\repository\source\AbstractRepository;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of ContentsSettings
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ContentsSettings extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\ContentSetting';
    }
    
    /**
     * Get Configuration of product/addon/domain
     * 
     * @since 3.0.0
     * @param type $cid
     * @return type
     */
    public function getConfigByContentId($cid)
    {
        $query = DB::table("ResellersCenter_GroupsContentsSettings");
        $data = $query->where("relid", "=" , $cid)->get();

        $result = array();
        foreach($data as $row)
        {
            if($row->setting == 'counting_type') {
                $result["type"] = $row->value;
            }
            else {
                $result["settings"][$row->setting] = $row->value;
            }
        }
        
        return $result;
    }
    
    /**
     * Save configuration of product/addon/domain
     * 
     * @since 3.0.0
     * @param type $relid
     * @param type $groupid
     * @param type $data
     */
    public function saveConfiguration($relid, $groupid, $data)
    {
        //Get rid of all pervious settings for selected product in group
        $query = DB::table("ResellersCenter_GroupsContentsSettings");
        $query->where("relid", $relid)->where("group_id", $groupid)->delete();
        
        //Add new settings
        foreach($data as $setting => $value)
        {
            $model = new ContentSetting();
            $model->relid   = $relid;
            $model->group_id = $groupid;
            $model->setting = $setting;
            $model->value   = $value;
            $model->save();
        }
    }
    
}
