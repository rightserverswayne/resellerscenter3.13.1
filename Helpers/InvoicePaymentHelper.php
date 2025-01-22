<?php

namespace MGModule\ResellersCenter\Helpers;

use MGModule\ResellersCenter\Core\Whmcs\Services\AbstractService;
use MGModule\ResellersCenter\models\whmcs\Invoice;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;

class InvoicePaymentHelper
{
    public static function makeInvoicePayment(Invoice $invoice)
    {
        foreach ($invoice->items as $item) {
            $service = $item->getServiceAttribute();
            if (!is_subclass_of($service, AbstractService::Class)) {
                continue;
            }
            $service->makePayment($item);
            $item->type = InvoiceItems::TYPE_COMPLETED_PREFIX . $item->type . InvoiceItems::TYPE_COMPLETED_SUFFIX;
            $item->save();
        }
    }
}