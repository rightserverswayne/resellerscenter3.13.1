<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class CustomFields extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\CustomField';
    }
    
    public function getClientFields($includeAdminOnly = false)
    {
        $model = $this->getModel();
        
        if ($includeAdminOnly) {
            return $model->where("type", "client")->where("adminonly", "!=", "on")->get();
        }

        return $model->where("type", "client")->get();
    }
    
    public function getProductFields()
    {
        $model = $this->getModel();
        $fields = $model->where("type", "product")->get();
        
        return $fields;
    }
}
