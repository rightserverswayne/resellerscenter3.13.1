<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of PaymentGateways
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class PaymentGateways extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\PaymentGateway';
    }
    
    public function getGatewaySettings($gateway)
    {
        $model = $this->getModel();
        $data = $model->where("gateway", $gateway)->get();
        
        $result = array();
        foreach($data as $config)
        {
            $result[$config->setting] = $config->value;
        }

        return $result;
    }
    
    public function getEnabledGatewaysArray()
    {
        $gateways = DB::table("tblpaymentgateways")->get();
        
        $result = array();
        foreach($gateways as $data) {
            $result[$data->gateway][$data->setting] = $data->value;
        }
        
        //get only visible gateways
        foreach($result as $gateway => $settings)
        {
            if($settings["visible"] != "on") {
                unset($result[$gateway]);
            }
        }
        
        return $result;
    }
}
