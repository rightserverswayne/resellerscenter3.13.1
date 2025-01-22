<?php

namespace MGModule\ResellersCenter\Helpers;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\repository\ResellersSettings;

class RcInvoiceNumberingHelper
{
    public static function decrementNextInvoiceNumber(Reseller $reseller)
    {
        $format = $reseller->settings->private->invoicenumber;
        $currentNumber = $reseller->settings->private->nextinvoicenumber;

        if (empty($format) || $currentNumber <= 1) {
            return;
        }

        $currentNumber--;
        $resellerSettings = new ResellersSettings();
        $resellerSettings->saveSingleSetting($reseller->id, 'nextinvoicenumber', $currentNumber, true);
    }
}