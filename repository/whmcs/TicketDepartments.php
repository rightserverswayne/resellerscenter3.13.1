<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;
use \MGModule\ResellersCenter\models\whmcs\TicketDepartment;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class TicketDepartments extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\TicketDepartment';
    }
    
    public function getAllDepartmentsSorted($isClientLogged = false)
    {
        $model = $this->getModel();
        
        if(!$isClientLogged)
        {
            return $model->where('clientsonly', '=', '')->orderBy('order', 'ASC')->get();
        }
        
        return $model->orderBy('order', 'ASC')->get();   
    }
}
