<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Taxes extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Tax';
    }
    
    public function getTax($level, $state = "", $country = "")
    {
        $model = $this->getModel();
        $tax = $model->where("level", $level)->where("state", $state)->where("country", $country)->first();
        
        if(empty($tax))
        {
            $tax = $model->where("level", $level)->where("country", $country)->first();
            
            if(empty($tax)) {
                $tax = $model->where("level", $level)->first();
            }
        }
        
        if(empty($tax->name)) 
        {
            global $whmcs;
            if(!$tax)
            {
                $tax = new \stdClass();
            }
            $tax->name = $whmcs->get_lang("invoicestax");
        }
        
        return $tax;
    }

    public function getOnlyTaxesThatApply( $level = 1, $country = '', $state = '')
    {
        $model = $this->getModel();
        $model = $model->where('level', $level);

        $countryIn  = $country ? ['', $country] : [''];
        $stateIn    = $state ? ['', $state] : [''];
     
        $model = $model->whereIn('country',  $countryIn);
        $model = $model->whereIn('state',  $stateIn);
        
        return $model->first();
    }
}
