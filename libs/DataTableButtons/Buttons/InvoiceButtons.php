<?php

namespace MGModule\ResellersCenter\libs\DataTableButtons\Buttons;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\libs\DataTableButtons\ButtonInterface;
use MGModule\ResellersCenter\mgLibs\Lang;

class InvoiceButtons implements ButtonInterface
{

    public function getButtons(Reseller $reseller):array
    {
        $rcInvoicesButton = [[
            "type" => "only-icon",
            "class" => "openEditInvoice btn-primary",
            "data" => ["invoiceid" => "id"],
            "icon" => "fa fa-list-ul",
            "tooltip" => Lang::absoluteT('addonCA', 'invoices','table','detailsInfo')]];

        $whmcsInvoicesButton = [[
            "type" => "only-icon",
            "class" => "openDetailsInvoice btn-primary",
            "data" => ["invoiceid" => "id"],
            "icon" => "fa fa-list-ul",
            "tooltip" => Lang::absoluteT('addonCA', 'invoices','table','detailsInfo')]];

        return $reseller->settings->admin->resellerInvoice ? $rcInvoicesButton : $whmcsInvoicesButton;
    }
}