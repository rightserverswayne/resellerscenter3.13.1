<?php

namespace MGModule\ResellersCenter\libs\CreditLine\Helpers;

class OrderActivator
{
    public static function activeOrderByInvoice($invoice): bool
    {
        foreach ($invoice->items as $item) {
            if (!$item->exists) {
                continue;
            }

            $service = $item->getServiceAttribute();

            if ($service->exists) {
                return $service->activateOrder();
            }
        }
        return false;
    }

}