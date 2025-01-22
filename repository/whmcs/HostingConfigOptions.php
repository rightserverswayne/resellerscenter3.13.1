<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of Addons
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class HostingConfigOptions extends AbstractRepository
{    
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\HostingConfigOption';
    }

    /**
     * Get values of configurable options
     *
     * @param $relid
     * @param $configid
     * @return mixed
     */
    public function getByRelidAndConfig($relid, $configid)
    {
        $model  = $this->getModel();
        $result = $model->where("relid", $relid)->where("configid", $configid)->first();

        return $result;
    }
}
