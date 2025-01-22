<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of TldCategories
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class TldCategories extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\TldCategory';
    }
    
    public function getWithIds($categoriesids)
    {
        $model = $this->getModel();
        $result = $model->whereIn("id", $categoriesids)->get();
        return $result;
    }
}
