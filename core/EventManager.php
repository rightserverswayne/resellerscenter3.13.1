<?php
namespace MGModule\ResellersCenter\Core;
use MGModule\ResellersCenter\core\Logger;

/**
 * Description of EventManager
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class EventManager 
{
    public static $receivers = array();
    
    public static function load($receivers)
    {
        foreach($receivers as $key => $rec)
        {
            self::register($rec, $key);
        }
        
        return true;
    }
    
    public static function register($receiver, $key = null)
    {
        foreach(self::$receivers as $rec)
        {
            if($rec === $receiver)
            {
                return false;
            }
        }
        
        if($key != null)
        {
            self::$receivers[$key] = $receiver;
        }
        else
        {
            self::$receivers[] = $receiver;
        }
        
        return true;
    }
        
    public static function unregister($receiver, $key = null)
    {
        if($key != null)
        {
            unset(self::$receivers[$key]);
            return true;
        }
        
        foreach(self::$receivers as $key=>$rec)
        {
            if($rec === $receiver)
            {
                unset(self::$receivers[$key]);
                return true;
            }
        }
        
        return false;
    }
       
    public static function call($eventName /* params */)
    {
        $params = func_get_args();
        unset($params[0]);
        
        $results = array();
        foreach(self::$receivers as $key => $receiver)
        {
            if(!method_exists($receiver, $eventName))
            {
                continue;
            }

            $results[$key] = call_user_func_array(array($receiver, $eventName), $params);
        }
        
        //Always create logs messages
        Logger::createLog($eventName, $params);
        
        return $results;
    }
}
