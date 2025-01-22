<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of TldCategoriesPivot
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class TldCategoriesPivot extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\TldCategoryPivot';
    }
    
    public function getByTldId($tldid)
    {
        $model = $this->getModel();
        return $model->where("tld_id", $tldid)->get();
    }
}
