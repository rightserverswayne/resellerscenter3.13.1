<?php

namespace MGModule\ResellersCenter\libs\DataTableButtons\Buttons;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\libs\DataTableButtons\ButtonInterface;
use MGModule\ResellersCenter\mgLibs\Lang;

class ServiceButtons implements ButtonInterface
{
    const SUSPENDED_STATUS = 'Suspended';

    public function getButtons(Reseller $reseller):array
    {
        $buttons[] =
            ["type"    => "only-icon",
                "class"   => "openDeleteService btn-danger",
                "data"    => ["serviceid" => "id"],
                "icon"    => "fa fa-trash-o",
                "tooltip" => Lang::absoluteT('addonCA', 'clients','services', 'table', 'deleteInfo')
            ];

        if ($reseller->settings->admin->suspend == 'on' || $reseller->settings->private->suspend == 'on') {
            $buttons[] = [
                "type"    => "only-icon",
                "class"   => "openUnsuspendService btn-info",
                "data"    => ["serviceid" => "id"],
                "icon"    => "fa fa-unlock",
                "tooltip" => Lang::absoluteT('addonCA', 'orders','table', 'unsuspendService'),
                "if"      => [["status", "==", self::SUSPENDED_STATUS]]
            ];
            $buttons[] = [
                "type"    => "only-iconn",
                "class"   => "openSuspendService btn-warning",
                "data"    => ["serviceid" => "id"],
                "icon"    => "fa fa-lock",
                "tooltip" => Lang::absoluteT('addonCA', 'orders','table', 'suspendService'),
                "if"      => [["status", "!=", self::SUSPENDED_STATUS]]
            ];
        }

        if (!$reseller->settings->admin->hideClientLogin
            && ($reseller->settings->admin->login == 'on')) {
            $buttons[] = [
                "type"    => "only-icon",
                "class"   => "loginAndShowServiceBtn btn-default btn-info",
                "data"    => ["clientid" => "client_id", "serviceid" => "id"],
                "icon"    => "fa fa-sign-in",
                "tooltip" => Lang::absoluteT('addonCA', 'clients','table', 'loginAndShowServiceInfo')
            ];
        }

        return $buttons;
    }
}