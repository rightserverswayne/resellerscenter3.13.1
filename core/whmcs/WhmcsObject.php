<?php
namespace MGModule\ResellersCenter\Core\Whmcs;

/**
 * Description of WhmcsObject
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
abstract class WhmcsObject
{
    /**
     * Transaction id
     *
     * @var int 
     */
    protected $id;
    
    /**
     * Transaction model
     *
     * @var \MGModule\ResellersCenter\models\whmcs\ 
     */
    protected $model;
    
    /**
     * Save id
     * 
     * @param id $id
     */
    public function __construct($idOrModel = null)
    {
        if(!empty($idOrModel))
        {
            if(is_numeric($idOrModel))
            {
                $this->id = $idOrModel;
            }
            else
            {
                $this->model = $idOrModel;
                $this->id    = $this->model->id;
            }
        }
    }
    
    public function __get($name)
    {
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
    
    public function create($params)
    {
        $classname = $this->getModelClass();
        $repo = new $classname;
        
        $new = $repo->create($params);
        $this->id = $new->id;
        return $this; 
    }
    
    protected function load()
    {
        $classname = $this->getModelClass();
        $repo = new $classname;
        $this->model = $repo->find($this->id);
    }
    
    abstract protected function getModelClass();
}
