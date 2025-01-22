<?php
namespace MGModule\ResellersCenter\core;

class WHMCSGlobalConfig
{
    private static $config = [];
    private static $fromname = null;
    private static $fromemail = null;
  
    public static function storeConfig($configKeys = [])
    {
        if($configKeys)
        {
            global $CONFIG;
            foreach($configKeys as $key)
            {
                self::$config[$key] = $CONFIG[$key];
            }
        }
    }
    
    public static function restoreConfig($configKeys = [])
    {
        if($configKeys)
        {
            global $CONFIG;
            foreach($configKeys as $key)
            {
                $CONFIG[$key] = self::$config[$key];
            }
        }
    }
    
    public static function storeEmailConfig()
    {
        global $fromname;
        global $fromemail;
        
        self::$fromname     = $fromname;
        self::$fromemail    = $fromemail;
    }
    
    public static function restoreEmailConfig()
    {
        global $fromname;
        global $fromemail;
        
        $fromname   = self::$fromname;
        $fromemail  = self::$fromemail;
    }
}