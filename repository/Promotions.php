<?php
namespace MGModule\ResellersCenter\repository;
use MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of Promotions
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Promotions extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\Promotion';
    }
    
    public function getByCode($code)
    {
        $model = $this->getModel();
        $result = $this->model->where("code", $code)->first();
        
        return $result;
    }
}