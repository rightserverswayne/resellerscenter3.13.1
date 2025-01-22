<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of Tlds
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Tlds extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Tld';
    }
    
    public function getByTld($tld)
    {
        $model = $this->getModel();
        $result = $model->where("tld", $tld)->first();
        
        return $result;
    }
}
