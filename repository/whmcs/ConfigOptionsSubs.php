<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of ConfigOptionGroups
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ConfigOptionsSubs extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\ConfigOptionSub';
    }
    
    public function getByConfigId($conifgid)
    {
        $model = $this->getModel();
        $result = $model->where("configid", $conifgid)->get();
        
        return $result;
    }
}
