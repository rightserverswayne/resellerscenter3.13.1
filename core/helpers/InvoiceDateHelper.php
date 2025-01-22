<?php

namespace MGModule\ResellersCenter\Core\Helpers;

use DateInterval;
use DateTime;

class InvoiceDateHelper
{
    public static function generateDefaultInvoiceDates()
    {
        global $whmcs;
        $nowDate = new DateTime("NOW");
        $dueDateDays = $whmcs->get_config("CreateInvoiceDaysBefore");
        $interval = new DateInterval("P{$dueDateDays}D");
        $dueDate = clone $nowDate;
        $dueDate->add($interval);
        return ['date'=> $nowDate->format("Y-m-d"), 'duedate'=> $dueDate->format("Y-m-d")];
    }
}