<?php
namespace MGModule\ResellersCenter\models\source;

/**
 * Description of Validator
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Validator 
{
    /**
     * Check if provided value is empty
     * 
     * @since 3.0.0
     * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
     * @param string $value
     * @return boolean
     */
    public static function isEmpty($value)
    {
        if(empty($value))
        {
            return true;
        }
         
       return false;
       
       //return !empty($value);
    }
    
    /**
     * Check if provided value is number
     * 
     * @since 3.0.0
     * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
     * @param string $value
     * @return boolean
     */
    public static function isNumber($value)
    {
        if(is_numeric($value))
        {
            return true;
        }
         
       return false;
    }
}
