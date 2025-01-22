<?php
namespace MGModule\ResellersCenter\repository\whmcs;

use \MGModule\ResellersCenter\repository\source\AbstractRepository;

class Invites extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Invite';
    }
    
    public function getLastest()
    {
        $model = $this->getModel();
        $invite = $model->orderBy("id", "desc")->first();
        
        return $invite;
    }
}
