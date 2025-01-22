<?php

namespace MGModule\ResellersCenter\libs\DataTableButtons\Buttons;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\libs\DataTableButtons\ButtonInterface;
use MGModule\ResellersCenter\mgLibs\Lang;

class AddonButtons implements ButtonInterface
{

    public function getButtons(Reseller $reseller):array
    {
        $buttons[] = [
                    "type"    => "only-icon",
                    "class"   => "openDeleteAddon btn-danger",
                    "data"    => ["addonid" => "id"],
                    "icon"    => "fa fa-trash-o",
                    "tooltip" => Lang::absoluteT('addonCA', 'orders','table', 'addons', 'deleteInfo')
                ];

        if (!$reseller->settings->admin->hideClientLogin
            && ($reseller->settings->admin->login == 'on')) {
            $buttons[] = [
                "type"    => "only-icon",
                "class"   => "loginAndShowAddonBtn btn-default btn-info",
                "data"    => ["clientid" => "client_id", "addonid" => "id"],
                "icon"    => "fa fa-sign-in",
                "tooltip" => Lang::absoluteT('addonCA', 'clients','table', 'loginAndShowAddonInfo')
            ];
        }

        return $buttons;
    }
}