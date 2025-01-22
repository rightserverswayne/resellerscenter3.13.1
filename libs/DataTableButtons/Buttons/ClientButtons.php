<?php

namespace MGModule\ResellersCenter\libs\DataTableButtons\Buttons;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\libs\DataTableButtons\ButtonInterface;
use MGModule\ResellersCenter\mgLibs\Lang;

class ClientButtons implements ButtonInterface
{
    public function getButtons(Reseller $reseller):array
    {
        $buttons = [];

        if (!$reseller->settings->admin->hideClientLogin
            && ($reseller->settings->admin->login == 'on')) {
            $buttons[] = [
                "type"    => "only-icon",
                "class"   => "loginAsClientBtn btn-default",
                "data"    => ["clientid" => "client_id"],
                "icon"    => "fa fa-user",
                "tooltip" => Lang::absoluteT('addonCA', 'clients','table', 'loginAsClientInfo')
            ];
        }

        if ($reseller->settings->admin->order == 'on') {
            $buttons[] = [
                "type"    => "only-icon",
                "class"   => "openAddOrderClient btn-warning",
                "data"    => ["clientid" => "client_id"],
                "icon"    => "fa fa-shopping-cart",
                "tooltip" => Lang::absoluteT('addonCA', 'clients','table', 'makeOrderInfo')
            ];
        }

        if (!$reseller->settings->admin->hideClientDetails) {
            $buttons[] = [
                "type"    => "only-icon",
                "class"   => "openDetailsClient btn-info",
                "data"    => ["clientid" => "client_id"],
                "icon"    => "fa fa-pencil-square-o",
                "tooltip" => Lang::absoluteT('addonCA', 'clients','table', 'detailsInfo')
            ];
        }

        if ($reseller->settings->admin->hideDelete != 'on') {
            $buttons[] = [
                "type"    => "only-icon",
                "class"   => "openDeleteClient btn-danger",
                "data"    => ["clientid" => "client_id"],
                "icon"    => "fa fa-trash-o",
                "tooltip" => Lang::absoluteT('addonCA', 'clients','table', 'deleteInfo')
            ];
        }

        return $buttons;
    }
}