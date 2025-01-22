<?php

namespace MGModule\ResellersCenter\Core\Traits;

/**
 * Description of HasModel
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */

trait HasModel
{
    /**
     * Property object parent
     *
     * @var
     */
    protected $model;

    /**
     * Get model class
     *
     * @return mixed
     */
    abstract function getModelClass();

    /**
     * Get value from model
     * Notice: will be overridden by HasProperty __get method
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        //Check if the model is already loaded and get value
        $this->model ?: $this->load();
        $result = $this->model->{$name};

        return $result;
    }

    /**
     * Set value in model
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->model ?: $this->load();
        $this->model->{$name} = $value;
    }

    /**
     * Call method from model object
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $this->model ?: $this->load();
        $result = $this->model->{$name}($arguments);

        return $result;
    }

    /**
     * Initialize trait
     *
     * @param $model
     */
    protected function initHasModel($model)
    {
        $this->model = $model;
    }

    /**
     * Load and find model in database
     */
    protected function load()
    {
        $classname  = $this->getModelClass();
        $model      = new $classname();

        $this->model = $this->id === null ? null : $model->find($this->id);
    }
}