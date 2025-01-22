<?php

namespace MGModule\ResellersCenter\Core\Helpers;

class InvoiceNumberingHelper
{
    const TAX_NEXT_CUSTOM_INVOICE_NUMBER = "TaxNextCustomInvoiceNumber";

    public static function decrementTaxCustomInvoiceNumber()
    {
        $nextNumber = \WHMCS\Config\Setting::getValue(self::TAX_NEXT_CUSTOM_INVOICE_NUMBER);
        \WHMCS\Config\Setting::setValue(self::TAX_NEXT_CUSTOM_INVOICE_NUMBER, self::padAndDecrement($nextNumber));
    }

    private static function padAndDecrement($number)
    {
        $newNumber = --$number;
        if (substr($number, 0, 1) == "0") {
            $numberLength = strlen($number);
            $newNumber = str_pad($newNumber, $numberLength, "0", STR_PAD_LEFT);
        }
        return $newNumber;
    }
}