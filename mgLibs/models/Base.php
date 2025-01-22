<?php

namespace MGModule\ResellersCenter\mgLibs\models;
use MGModule\ResellersCenter as main;

/**
 * Description of abstractModel
 *
 * @author Michal Czech <michael@modulesgarden.com>
 * @SuppressWarnings(PHPMD)
 */
abstract class Base {   
    /**
     * Normalized Time Stamp
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param string $strTime
     * @return string
     */
    static function timeStamp($strTime = 'now')
    {
        return date('Y-m-d H:i:s',  strtotime($strTime));
    }
    
    /**
     * Disable Get Function
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param string $property
     * @throws main\mgLibs\exceptions\System
     */
    function __get($property) {
        throw new main\mgLibs\exceptions\System('Property: '.$property.' does not exits in: '.get_called_class(), main\mgLibs\exceptions\Codes::PROPERTY_NOT_EXISTS);
    }
    
    /**
     * Disable Set Function
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param string $property
     * @param string $value
     * @throws main\mgLibs\exceptions\System
     */
    function __set($property, $value) {
        throw new main\mgLibs\exceptions\System('Property: '.$property.' does not exits in: '.get_called_class(), main\mgLibs\exceptions\Codes::PROPERTY_NOT_EXISTS);
    }
    
    /**
     * Disable Call Function
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param string $function
     * @param string $params
     * @throws main\mgLibs\exceptions\System
     */
    function __call($function, $params) {
        throw new main\mgLibs\exceptions\System('Function: '.$function.' does not exits in: '.get_called_class(), main\mgLibs\exceptions\Codes::PROPERTY_NOT_EXISTS);
    }
    
    /**
     * Cast To array
     * 
     * @param string $container
     * @return array
     */
    function toArray($container = true){
        $className = get_called_class();
        
        $fields = get_class_vars($className);

        foreach(explode('\\', $className) as $className);
        
        $data = array();
        
        foreach($fields as $name => $defult)
        {
            if(isset($this->{$name}))
            {
                $data[$name] = $this->{$name};
            }
        }

        if($container === true)
        {
            return array(
                $className => $data
            );
        }
        elseif($container)
        {
            return array(
                $container => $data
            );
        }
        else
        {
            return $data;
        }
    }
    
    /**
     * Encrypt String using Hash from configration
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param string $input
     * @return string
     */
    function encrypt($input) {
        if(empty($input))
        {
            return false;
        }
                        
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, main\mgLibs\process\MainInstance::I()->getEncryptKey(), $input, MCRYPT_MODE_ECB));
    }
    
    /**
     * Decrypt String using Hash from configration
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param string $input
     * @return string
     */
    function decrypt($input) {
        if(empty($input))
        {
            return false;
        }
                
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, main\mgLibs\process\MainInstance::I()->getEncryptKey(), base64_decode($input), MCRYPT_MODE_ECB));
    }
    
    function serialize($input){
        return base64_encode(serialize($input));
    }
    
    function unserialize($input){
        return unserialize(base64_decode($input));
    }
}
