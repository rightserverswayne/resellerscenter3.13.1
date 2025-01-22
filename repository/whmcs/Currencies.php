<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Currencies extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Currency';
    }
    
    public function getDefault()
    {
        $model = $this->getModel();
        $result = $model->where("default", 1)->first();
        
        return $result;
    }
    
    public function getAvailableCurrencies()
    {
        $model = $this->getModel();
        $result = $model->get();
        
        return $result;
    }
    
    public function getByCode($code)
    {
        $model = $this->getModel();
        $result = $model->where("code", $code)->first();
        
        return $result;
    }
}
