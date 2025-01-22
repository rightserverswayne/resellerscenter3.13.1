<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Logs;

use MGModule\ResellersCenter\Core\Whmcs\WhmcsObject;
use MGModule\ResellersCenter\repository\whmcs\ActivityLogs;

/**
 * Description of ActivityLog.php
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */

class ActivityLog extends WhmcsObject
{
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\models\whmcs\ActivityLog::class;
    }

    public static function getByDescription($description)
    {
        $repo = new ActivityLogs();
        $model = $repo->getByDescriptionLike($description, true);

        return new ActivityLog($model);
    }
}