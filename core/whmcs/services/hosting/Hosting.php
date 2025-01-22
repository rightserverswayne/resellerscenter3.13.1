<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Services\Hosting;

use MGModule\ResellersCenter\Core\Whmcs\Products\Products\Product;
use MGModule\ResellersCenter\Core\Whmcs\Services\AbstractService;
use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;
use MGModule\ResellersCenter\models\whmcs\InvoiceItem;

/**
 * Description of Hosting
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Hosting extends AbstractService
{
    /**
     * Get hosting upgrades
     *
     * @param Product $product
     * @param $billingcycle
     * @return Upgrade
     */
    public function getUpgrade(Product $product, $billingcycle)
    {
        return new Upgrade($this, $product, $billingcycle);
    }

    /**
     * Terminate service
     *
     * @throws \MGModule\ResellersCenter\mgLibs\exceptions\WhmcsAPI
     */
    public function terminate()
    {
        if (!empty($this->product->servertype))
        {
            WhmcsAPI::request("ModuleTerminate", ["accountid" => $this->id, "serviceid" => $this->id]);
        }
        else
        {
            WhmcsAPI::request("UpdateClientProduct", [
                "serviceid"       => $this->id,
                "status"          => "Terminated",
                "terminationDate" => date("Y-m-d")
            ]);
        }
    }

    /**
     * @return string
     */
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\models\whmcs\Hosting::class;
    }

    /**
     * Get product ID
     *
     * @return mixed
     */
    protected function getProductRelid()
    {
        return $this->packageid;
    }

    public function makePayment(InvoiceItem $invoiceItem)
    {
        if (!function_exists("makeHostingPayment")) {
            require_once ROOTDIR.DS."includes".DS."invoicefunctions.php";
        }
        makeHostingPayment($invoiceItem->relid, $invoiceItem->invoice);
    }
}