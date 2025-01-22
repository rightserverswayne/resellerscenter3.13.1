<?php

namespace MGModule\ResellersCenter\Core\Resources\Invoices;

/**
 * Description of Decorator
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Decorator
{
    /**
     * Get Invoice status as text
     *
     * @return array
     */
    public static function getStatusText($status)
    {
        global $whmcs;

        $class = "";
        switch ($status)
        {
            case "unpaid":
                $class = "textred";
                break;
            case "paid":
                $class = "textgreen";
                break;
            case "cancelled":
                $class = "textgrey";
                break;
        }

        $statusText = $whmcs->get_lang("invoices{$status}");
        return "<span class='{$class}'>{$statusText}</span>";
    }
}
