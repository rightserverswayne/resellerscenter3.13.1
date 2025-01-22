<?php

namespace MGModule\ResellersCenter\libs\DataTableButtons\Buttons;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\libs\DataTableButtons\ButtonInterface;
use MGModule\ResellersCenter\mgLibs\Lang;

class TicketButtons implements ButtonInterface
{

    public function getButtons(Reseller $reseller):array
    {
        $buttons[] = [
            "type" => "only-icon",
            "class" => "openDetailsTicket btn-primary",
            "data" => ["ticketid" => "id"],
            "icon" => "fa fa-pencil-square-o",
            "tooltip" => Lang::absoluteT('addonCA', 'tickets','table','detailsInfo')
        ];

        $buttons[] = [
            "type" => "only-icon",
            "class" => "openDeleteTicket btn-danger",
            "data" => ["ticketid" => "id"],
            "icon" => "fa fa-trash-o",
            "tooltip" => Lang::absoluteT('addonCA', 'tickets','table','deleteInfo')
        ];

        if (!$reseller->settings->admin->hideClientLogin
            && ($reseller->settings->admin->login == 'on')) {
            $buttons[] = [
                "type"    => "only-icon",
                "class"   => "loginAndShowTicketBtn btn-default btn-info",
                "data"    => ["clientid" => "client_id", "ticketid" => "id"],
                "icon"    => "fa fa-sign-in",
                "tooltip" => Lang::absoluteT('addonCA', 'clients','table', 'loginAndShowTicketInfo')
            ];
        }

        return $buttons;
    }
}