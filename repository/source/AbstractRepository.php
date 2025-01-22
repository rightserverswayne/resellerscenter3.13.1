<?php
namespace MGModule\ResellersCenter\Repository\Source;

use \Illuminate\Database\Eloquent\Model;
use MGModule\ResellersCenter\repository\source\RepositoryException;

/**
 * Description of AbstractRepository
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
abstract class AbstractRepository 
{
    /**
     * Container for created model object
     * 
     * @var
     */
    protected $model;


    /**
     * Force to Specify Model class name in repository
     *
     * @return mixed
     */
    abstract function determinateModel();

    
    /**
     * @throws \Bosnadev\Repositories\Exceptions\RepositoryException
     */
    public function __construct() 
    {
        $this->makeModel();
    }

    /**
     * Return model
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Make model object
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws mgCRM2\Repositories\Source\RepositoryException
     */
    public function makeModel()
    {
        $this->model = $this->determinateModel();
        $this->model = new $this->model;

        if ( ! $this->model instanceof Model ) 
        {
            $classname = get_class($this->model);
            throw new RepositoryException("Class {$classname} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model;
    }



    /**
     * Return all elements from model
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->model->all();
    }


    /**
     * Delete the model from the database.
     *
     * @return bool|null
     */
    public function delete($id)
    {
        $item = $this->model->find($id);
        if($item->exists == null) {
            throw new RepositoryException("item_not_found");
        }
        
        return $this->model->find($id)->delete();
    }

    /**
     * Force to delete resource from Database
     * This is wrapper for Eloquent action
     *
     * @param type $id
     * @return void
     */
    public function forceDelete($id)
    {
        return $this->model->find($id)->forceDelete();
    }

    /**
     * return single model follow by id
     *
     * @param type $id
     * @return Illuminate\Database\Eloquent\Model
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Wrap model to order functionality
     *
     * @param type $column
     * @param type $direction
     * @return Illuminate\Database\Eloquent\Model
     */
    public function orderBy($column, $direction = 'ASC')
    {
        return $this->model->orderBy($column, $direction);
    }

    /**
     * Wrap model to where functionality
     *
     * @param type $column
     * @param type $direction
     * @return Illuminate\Database\Eloquent\Model
     */
    public function where($column, $operand, $value = null)
    {
        return $this->model->where($column, $operand, $value);
    }

    /**
     * Create in DB
     *
     * @param type $data
     * @return type
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update in database
     * 
     * @param int $id
     * @param array $data
     */
    public function update($id, array $data)
    {
        $model = $this->getModel();
        $model->find($id)->update($data);
    }
}
