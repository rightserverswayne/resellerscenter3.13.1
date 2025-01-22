<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use MGModule\ResellersCenter\repository\source\AbstractRepository;
use MGModule\ResellersCenter\models\whmcs\Configuration as ConfigModel;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Configuration
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Configuration extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Configuration';
    }
    
    public function saveSetting($name, $value)
    {
        //Delete old one
        DB::table("tblconfiguration")->where("setting", $name)->delete();
        
        //Save new
        $model = new ConfigModel();
        $model->setting = $name;
        $model->value = $value;
        
        $model->save();
    }
    
    public function getSetting($name)
    {
        $model = $this->getModel();
        $setting = $model->where("setting", $name)->first();
        
        return $setting->value;
    }
}
