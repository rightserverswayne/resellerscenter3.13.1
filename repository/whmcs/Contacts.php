<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of Contacts
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Contacts extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Contact';
    }
    
    public function getByClientId($clientid)
    {
        $model = $this->getModel();
        $contacts = $model->where("userid", $clientid)->get();
        
        return $contacts;
    }
}
