<?php
namespace MGModule\ResellersCenter\Repository\Whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of Addons
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Addons extends AbstractRepository
{    
    const BILLINGCYCLE_FREE = 'free';
    
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Addon';
    }
    
    public function getAvailable()
    {
        $model = $this->getModel();
        $result = $model->where("billingcycle", "!=", self::BILLINGCYCLE_FREE)->get();
        
        return $result;
    }
}
