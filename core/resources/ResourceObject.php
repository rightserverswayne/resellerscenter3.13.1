<?php
namespace MGModule\ResellersCenter\Core\Resources;

/**
 * Description of ResourceObject
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
abstract class ResourceObject
{
    /**
     * Object id
     *
     * @var int 
     */
    protected $id;

    /**
     * Transaction model
     *
     * @var \MGModule\ResellersCenter\models\*
     */
    protected $model;

    /**
     * ResourceObject constructor.
     *
     * @param null $idOrModel
     */
    public function __construct($idOrModel = null)
    {
        $this->initResource($idOrModel);
    }

    /**
     * Initialize Resource object by setting ID or model
     *
     * @param $idOrModel
     */
    protected function initResource($idOrModel)
    {
        if(is_numeric($idOrModel))
        {
            $this->id = $idOrModel;
        }
        elseif(is_object($idOrModel))
        {
            $this->model = $idOrModel;
            $this->id    = $this->model->id;
        }
    }

    public function __get($name)
    {
        //load property
        if(in_array($name, array_keys(get_object_vars($this))) && !in_array($name, ["id", "model"]))
        {
            if(empty($this->{$name}))
            {
                $property = ucfirst((new \ReflectionClass($this))->getNamespaceName() . "\\{$name}");
                $this->{$name} = new $property($this);
            }

            return $this->{$name};
        }

        //load model if not loaded
        if(empty($this->model))
        {
            $this->load();
        }

        return $this->model->{$name};
    }

    public function __set($name, $value)
    {
        //load model if not loaded
        if(empty($this->model))
        {
            $this->load();
        }

        $this->model->{$name} = $value;
    }

    public function __call($name, $arguments)
    {
        //load model if not loaded
        if(empty($this->model))
        {
            $this->load();
        }

        return $this->model->{$name}($arguments);
    }

    protected function load()
    {
        $classname = $this->getModelClass();
        $repo = new $classname();
        $this->model = $repo->find($this->id);
    }

    abstract protected function getModelClass();
}
