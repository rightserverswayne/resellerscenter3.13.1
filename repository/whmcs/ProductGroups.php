<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ProductGroups extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\ProductGroup';
    }
    
    public function getByName($name)
    {
        $model = $this->getModel();
        $result = $model->where("name", $name)->first();
        
        return $result;
    }
}
