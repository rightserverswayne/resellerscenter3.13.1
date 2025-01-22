<?php
namespace MGModule\ResellersCenter\core;

class StaticFields
{
    private static $maxInvoiceItemId = null;
  
    public static function storeMaxInvoiceItemId($id)
    {
        if($id)
        {
            self::$maxInvoiceItemId = $id;
        }
    }
    
    public static function getMaxInvoiceItemId($onlyOnce = true)
    {
        $id = self::$maxInvoiceItemId;
        if($onlyOnce)
        {
            self::$maxInvoiceItemId = null;
        }
        return $id;
    }
}