<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of AddonModules
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class AddonModules extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\AddonModule';
    }

    public function getByName($name)
    {
        $model = $this->getModel();
        $result = $model->where("module", $name)->get();

        return $result;
    }
}
