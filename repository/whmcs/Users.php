<?php
namespace MGModule\ResellersCenter\repository\whmcs;

use \MGModule\ResellersCenter\repository\source\AbstractRepository;

class Users extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\User';
    }
    
    public function getLastest()
    {
        $model = $this->getModel();
        $user = $model->orderBy("id", "desc")->first();
        
        return $user;
    }
}
