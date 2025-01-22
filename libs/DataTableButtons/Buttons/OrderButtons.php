<?php

namespace MGModule\ResellersCenter\libs\DataTableButtons\Buttons;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\libs\DataTableButtons\ButtonInterface;
use MGModule\ResellersCenter\mgLibs\Lang;

class OrderButtons implements ButtonInterface
{

    public function getButtons(Reseller $reseller):array
    {
        return [
            [
                "type" => "only-icon",
                "class" => "openDetailsOrder btn-primary",
                "data" => ["orderid" => "id", "paymentstatus" => "paymentstatus"],
                "icon" => "fa fa-list",
                "tooltip" => Lang::absoluteT('addonCA', 'orders','table','orderDetailsInfo')],
            [
                "type" => "only-icon",
                "class" => "openAcceptOrder btn-success",
                "data" => ["orderid" => "id", "paymentstatus" => "paymentstatus", "invoiceid" => "invoiceid"],
                "icon" => "fa fa-check-square-o",
                "if" => [["DT_RowClass", ""], ["status", "Pending"]],
                "tooltip" => Lang::absoluteT('addonCA', 'orders','table','acceptOrderInfo')],
        ];
    }
}