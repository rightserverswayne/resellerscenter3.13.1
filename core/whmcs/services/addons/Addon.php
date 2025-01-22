<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Services\Addons;

use MGModule\ResellersCenter\Core\Whmcs\Services\AbstractService;
use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;
use MGModule\ResellersCenter\models\whmcs\InvoiceItem;

/**
 * Description of Addon
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Addon extends AbstractService
{
    /**
     * Terminate addon
     *
     * @throws \MGModule\ResellersCenter\mgLibs\exceptions\WhmcsAPI
     */
    public function terminate()
    {
        WhmcsAPI::request("UpdateClientAddon", [
            "id"              => $this->id,
            "status"          => "Terminated",
            "terminationDate" => date("Y-m-d")
        ]);
    }

    /**
     * @return string
     */
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\models\whmcs\HostingAddon::class;
    }

    /**
     * @return int
     */
    protected function getProductRelid()
    {
        return $this->addonid;
    }

    public function makePayment(InvoiceItem $invoiceItem)
    {
        if (!function_exists("makeAddonPayment")) {
            require_once ROOTDIR.DS."includes".DS."invoicefunctions.php";
        }
        makeAddonPayment($invoiceItem->relid, $invoiceItem->invoice);
    }
}