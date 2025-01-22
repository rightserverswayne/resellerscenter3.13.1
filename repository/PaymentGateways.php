<?php
namespace MGModule\ResellersCenter\repository;
use MGModule\ResellersCenter\repository\source\AbstractRepository;
use MGModule\ResellersCenter\models\PaymentGateway;

/**
 * Description of Resellers
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class PaymentGateways extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\PaymentGateway';
    }
    
    public function getDefaultGateway($resellerid)
    {
        $model = $this->getModel();
        $result = $model->where("reseller_id", $resellerid)
                        ->where("setting", "enabled")
                        ->where("value", "on")
                        ->first();
        
        return $result->gateway;
    }
    
    public function getGatewaySettings($resellerid, $gateway)
    {
        $model = $this->getModel();
        $settings = $model->where("reseller_id", $resellerid)->where("gateway", $gateway)->get();
        
        $result = array();
        foreach($settings as $setting) {
            $result[$setting->setting] = $setting->value;
        }
        
        return $result;
    }
    
    public function saveGateway($resellerid, $gateway, $settings)
    {   
        //Remove old gatways settings
        $query = $this->getModel();
        $query->where("reseller_id", $resellerid)->where("gateway", $gateway)->delete();
        
        //Save new ones
        foreach($settings as $setting => $value)
        {
            $model = new PaymentGateway();
            $model->reseller_id = $resellerid;
            $model->gateway = $gateway;
            $model->setting = $setting;
            $model->value = $value;
            
            $model->save();
        }
    }
    
    public function updateSingleParam($resellerid, $gateway, $setting, $value)
    {
        $model  = $this->getModel();
        $model->where("reseller_id", $resellerid)->where("gateway", $gateway)->where("setting", $setting)->delete();
        
        $new = new PaymentGateway();
        $new->reseller_id = $resellerid;
        $new->gateway = $gateway;
        $new->setting = $setting;
        $new->value = $value;
        $new->save();
    }
}
