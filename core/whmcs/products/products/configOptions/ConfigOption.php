<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Products\Products\ConfigOptions;

use MGModule\ResellersCenter\Core\Traits\HasModel;
use MGModule\ResellersCenter\Core\Traits\HasProperties;
use MGModule\ResellersCenter\models\whmcs\ProductConfigOption;
use MGModule\ResellersCenter\repository\whmcs\ConfigOptions;

/**
 * Description of ConfigOption
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ConfigOption
{
    use HasModel, HasProperties
    {
        HasProperties::__get insteadof HasModel;
        HasModel::load as hasModelLoad;
    }

    /**
     * @var
     */
    public $value;

    /**
     * Config Option Type object
     *
     * @var Type
     */
    protected $type;

    /**
     * ConfigOption constructor.
     *
     * @param $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * @return string
     */
    protected function getModelClass()
    {
        return ProductConfigOption::class;
    }

    /**
     * Override type class
     *
     * @return array
     */
    protected function getOverriddenPropertiesClasses()
    {
        //Dynamically set type object class
        $namespace = "\MGModule\ResellersCenter\Core\Whmcs\Products\Products\ConfigOptions\\Types\\";
        $classname = ConfigOptions::TYPES_CLASSES[$this->model->optiontype];

        return
        [
            "type" =>
            [
                "class" => "{$namespace}{$classname}"
            ]
        ];
    }
}