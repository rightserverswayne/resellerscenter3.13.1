<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use MGModule\ResellersCenter\models\whmcs\ConfigOptionLink;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of Addons
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ConfigOptionsLinks extends AbstractRepository
{
    public function determinateModel()
    {
        return ConfigOptionLink::class;
    }

    /**
     * Get links from database
     *
     * @param $pid
     * @return mixed
     */
    public function getByProduct($pid)
    {
        $model = $this->getModel();
        $links = $model->byProduct($pid)->get();

        return $links;
    }
}
