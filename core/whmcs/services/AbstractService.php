<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Services;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Core\Whmcs\WhmcsObject;

use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;
use MGModule\ResellersCenter\models\whmcs\InvoiceItem;
use MGModule\ResellersCenter\repository\ResellersServices;
use MGModule\ResellersCenter\repository\whmcs\Orders;

/**
 * Description of AbstractService.php
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
abstract class AbstractService extends WhmcsObject
{
    /**
     * Get reseller object
     *
     * @return Reseller
     * @throws \ReflectionException
     */
    public function getReseller()
    {
        $repo = new ResellersServices();
        $service = $repo->getByTypeAndRelId($this->getType(), $this->id);

        return new Reseller($service->reseller);
    }

    /**
     * Get service product object
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function getRelatedProduct()
    {
        $type = ucfirst($this->getType());
        $type = ($type != "Hosting") ? $type : "Product";

        $classname = "\\MGModule\\ResellersCenter\\Core\\Whmcs\\Products\\{$type}s\\{$type}";
        $result = new $classname($this->getProductRelid(), $this->getReseller());
        return $result;
    }

    /**
     * Get the object product
     *
     * @return string
     * @throws \ReflectionException
     */
    protected function getType()
    {
        $class = (new \ReflectionClass($this))->getShortName();
        return strtolower($class);
    }

    public function activateOrder():bool
    {
        $ordersRepo = new Orders();
        $order = $ordersRepo->find($this->orderid);
        if ($order->status == Orders::STATUS_PENDING) {
            $command = 'AcceptOrder';
            $postData = [
                'orderid' => $order->id,
                'autosetup' => true,
                'sendemail' => true
            ];
            $result = localAPI($command, $postData, WhmcsAPI::getAdmin());

            return $result['result'] == 'success';
        } else {
            return false;
        }
    }

    public function getOrderId()
    {
        return $this->orderid;
    }

    abstract protected function getProductRelid();
    abstract public function makePayment(InvoiceItem $invoiceItem);
}