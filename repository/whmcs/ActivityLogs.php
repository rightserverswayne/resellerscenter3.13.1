<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use MGModule\ResellersCenter\models\whmcs\ActivityLog;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of ActivityLogs
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ActivityLogs extends AbstractRepository
{
    public function determinateModel()
    {
        return ActivityLog::class;
    }

    public function getByDescriptionLike($search, $last = false)
    {
        $model = $this->getModel();

        if($last)
        {
            $result = $model->where("description", "LIKE", "%$search%")->orderBy("id", "desc")->first();
        }
        else
        {
            $result = $model->where("description", "LIKE", "%$search%")->get();
        }

        return $result;
    }
}
