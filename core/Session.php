<?php
namespace MGModule\ResellersCenter\Core;
use MGModule\ResellersCenter\repository\SessionStorage;

/**
 * Description of SessionHelper
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Session 
{
    /**
     * Get variable from session
     * This function uses 'dot' notation to access values from array
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        if(strpos($name, ".") !== false)
        {
            $array = $_SESSION;
            $path = explode(".", $name);
            foreach($path as $element)
            {
                if(!is_array($array) || !isset($array[$element]))
                {
                    return $default;
                }
                
                $array = $array[$element];
            }
            
            return $array;
        }
        
        return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
    }
    
    /**
     * Set session variable
     * Use 'dot' notation to add values to array key
     * 
     * @param type $name
     * @param type $value
     */
    public static function set($name, $value)
    {
        \WHMCS\Session::set($name, $value);
    }
    
    /**
     * Unset Session variable
     * 
     * @param type $name
     */
    public static function clear($name)
    {
        unset($_SESSION[$name]);
    }
    
    /**
     * Get and unset session variable
     * 
     * @param type $name
     * @return type
     */
    public static function getAndClear($name)
    {
        $result = self::get($name);
        self::clear($name);
        
        return $result;
    }
    
    /**
     * Store current session in database.
     * Returns md5 key needed to retrive session.
     * 
     * @params type $time
     * @return $key
     */
    public static function store($time = 60)
    {
        //Generate key
        $key = md5(microtime().rand());
        
        //Save session
        $session = serialize($_SESSION);
        $storage = new SessionStorage();
        $storage->createNew($key, $time, $session);
        
        return $key;
    }
    
    /**
     * Retrive pervious stored session
     * 
     * @param type $key
     */
    public static function restore($key)
    {
        $storage = new SessionStorage();
        $session = $storage->getStoredByKey($key);
        
        if(!empty($session->value))
        {
            $_SESSION = unserialize($session->value);
        }
    }
}
