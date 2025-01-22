<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use MGModule\ResellersCenter\models\whmcs\ConfigOption;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of ConfigOptionGroups
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ConfigOptions extends AbstractRepository
{
    const TYPE_DROPDOWN = 1;

    const TYPE_RADIO    = 2;

    const TYPE_YES_NO   = 3;

    const TYPE_QUANTITY = 4;

    const TYPES_CLASSES =
    [
        1 => "Dropdown",
        2 => "Radio",
        3 => "YesNo",
        4 => "Quantity"
    ];

    /**
     * Set model
     *
     * @return string
     */
    public function determinateModel()
    {
        return ConfigOption::class;
    }

    /**
     * Get Config options available in product configuration
     *
     * @param $pid
     * @return mixed
     */
    public function getByProduct($pid)
    {
        $repo   = new ConfigOptionsLinks();
        $groups = $repo->getByProduct($pid)->pluck("gid")->toArray();

        $model   = $this->getModel();
        $options = $model->byInGroup($groups)->get();

        return $options;
    }

}
