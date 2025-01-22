<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Services\Domains;

use MGModule\ResellersCenter\Core\Whmcs\Services\AbstractService;

use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;
use MGModule\ResellersCenter\models\whmcs\InvoiceItem;
use MGModule\ResellersCenter\repository\whmcs\DomainPricing;
use MGModule\ResellersCenter\core\helpers\DomainHelper;
use MGModule\ResellersCenter\Core\Whmcs\Products\Domains\Domain as DomainProduct;

/**
 * Description of Domain
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Domain extends AbstractService
{
    /**
     * Set model for the object
     *
     * @return string
     */
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\models\whmcs\Domain::class;
    }

    /**
     * Get related product
     *
     * @return DomainProduct|mixed
     */
    public function getRelatedProduct()
    {
        $helper = new DomainHelper($this->domain);
        $domain = new DomainProduct($helper->getTLD(), $this->getReseller());

        return $domain;
    }

    /**
     * Change domain status
     *
     * @throws \MGModule\ResellersCenter\mgLibs\exceptions\WhmcsAPI
     */
    public function cancel()
    {
        WhmcsAPI::request("UpdateClientDomain", [
            "domainid"  => $this->id,
            "status"    => "Cancelled",
        ]);
    }

    /**
     * Get id from domain pricing table
     */
    protected function getProductRelid()
    {
        $helper = new DomainHelper($this->domain);
        $repo = new DomainPricing();
        $domain = $repo->getByTld($helper->getTLD());

        return $domain->id;
    }

    public function makePayment(InvoiceItem $invoiceItem)
    {
        if (!function_exists("makeDomainPayment")) {
            require_once ROOTDIR.DS."includes".DS."invoicefunctions.php";
        }
        makeDomainPayment($invoiceItem->relid, $invoiceItem->type);
    }
}