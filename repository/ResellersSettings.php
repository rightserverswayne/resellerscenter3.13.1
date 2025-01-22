<?php
namespace MGModule\ResellersCenter\repository;
use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\models\ResellerSetting;
use MGModule\ResellersCenter\repository\source\AbstractRepository;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Settings
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ResellersSettings extends AbstractRepository 
{
    const RESELLERS_DEFAULT_CONFIGURATION_ID = 0;
    
    public function determinateModel() 
    {
        return 'MGModule\ResellersCenter\models\ResellerSetting';
    }
    
    /**
     * 
     * @param type $name
     * @param type $value
     */
    public function getByNameAndValue($name, $value)
    {
        $model = $this->getModel();
        $result = $model->where("setting", $name)
                        ->where("value", $value)
                        ->get();
        
        return $result;
    }
    
    /**
     * Save settings for reseller
     * Resellerid no. 0 are default settings for all resellers.
     * 
     * @since 3.0.0
     * @param int $resellerid
     * @param type $settings
     */
    public function saveSettings($resellerid, $settings, $private = false)
    {
        $private = $private ? 1 : 0;

        //Clear configuration
        $query = DB::table("ResellersCenter_ResellersSettings");
        $query->where("reseller_id", $resellerid)->where("private", $private)->delete();

        foreach ($settings as $name => $value) {
            if (is_array($value)) {
                $value = serialize($value);
            }

            if ($name === 'domain') {
                $value = Server::getDomainWithoutWwwPrefix($value);
            }
            
            $value = preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, html_entity_decode($value));
            
            $rs = new ResellerSetting();
            $rs->fillData($resellerid, $name, $value, $private);
            $rs->save();
        }
    }
    
    public function saveSingleSetting($resellerid, $setting, $value, $private = false)
    {
        $private = $private ? 1 : 0;
        $model = $this->getModel();
        $model->where("reseller_id", $resellerid)->where("setting", $setting)->where("private", $private)->delete();
        
        $rs = new ResellerSetting();
        $rs->fillData($resellerid, $setting, $value, $private);
        $rs->save();
    }
    
    /**
     * Get reseller settings.
     * If there are no existing settings for reseller method 
     * will load default configuration. (reseller_id = 0)
     * 
     * @since 3.0.0
     * @param int $resellerid
     * @return type
     */
    public function getSettings($resellerid, $private = false)
    {
        $private = $private ? 1 : 0;
        
        $query = DB::table("ResellersCenter_ResellersSettings");
        $query->where("reseller_id", $resellerid)->where("private", $private);
        $settings = $query->get();
        
        //If settings does not exists take default ones
        if(empty($settings))
        {
            $query = DB::table("ResellersCenter_ResellersSettings");
            $query->where("reseller_id", self::RESELLERS_DEFAULT_CONFIGURATION_ID);
            $settings = $query->get();
        }
        
        $result = [];
        foreach ($settings as $setting) {
            //This will throw notice if string is not unserializabled!
            $array = unserialize($setting->value);
            if ($array !== false || $setting->value === 'b:0;') {
                $result[$setting->setting] = $array;
            } else {
                $result[$setting->setting] = $setting->value;
            }
        }
        
        return $result;
    }

    /**
     * Get single setting from reseller's configuration
     *
     * @param string   $setting
     * @param int      $resellerid
     * @param int|bool $private
     *
     * @return string|null
     */
    public function getSetting($setting, $resellerid, $private = false)
    {
        $private = $private ? 1 : 0;
        
        $model = new ResellerSetting();
        $result = $model->where("reseller_id", $resellerid)->where("setting", $setting)->where("private", $private)->first();
        
        return $result->value;
    }
    
    /**
     * Delete specifed setting and value from config of all resellers
     * 
     * @param type $setting
     * @param type $value
     */
    public function massDelete($setting, $value)
    {
        $model = new ResellerSetting();
        $model->where("setting", $setting)->where("value", $value)->delete();
    }
}
