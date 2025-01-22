<?php

namespace MGModule\ResellersCenter\libs\DataTableButtons\Buttons;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\libs\DataTableButtons\ButtonInterface;
use MGModule\ResellersCenter\mgLibs\Lang;

class DomainButtons implements ButtonInterface
{

    public function getButtons(Reseller $reseller):array
    {
        $buttons = [];

        $buttons[] =
            [
                "type"    => "only-icon",
                "class"   => "openDeleteDomain btn-danger",
                "data"    => ["domainid" => "id"],
                "icon"    => "fa fa-trash-o",
                "tooltip" => Lang::absoluteT('addonCA', 'orders','table', 'domains', 'deleteInfo')
            ];

        if (!$reseller->settings->admin->hideClientLogin
            && ($reseller->settings->admin->login == 'on')) {
            $buttons[] = [
                "type"    => "only-icon",
                "class"   => "loginAndShowDomainBtn btn-default btn-info",
                "data"    => ["clientid" => "client_id", "domainid" => "id"],
                "icon"    => "fa fa-sign-in",
                "tooltip" => Lang::absoluteT('addonCA', 'clients','table', 'loginAndShowDomainInfo')
            ];
        }

        return $buttons;
    }
}