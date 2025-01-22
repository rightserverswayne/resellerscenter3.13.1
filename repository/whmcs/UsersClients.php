<?php
namespace MGModule\ResellersCenter\repository\whmcs;

use \MGModule\ResellersCenter\repository\source\AbstractRepository;

class UsersClients extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\UserClients';
    }
    
    public function getLastest()
    {
        $model = $this->getModel();
        $userClient = $model->orderBy("id", "desc")->first();
        
        return $userClient;
    }
}
