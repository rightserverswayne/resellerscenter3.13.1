<?php
namespace MGModule\ResellersCenter\Core;

use MGModule\ResellersCenter\repository\whmcs\Configuration as WhmcsConfiguration;

/**
 * Description of Server
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Server
{
    /**
     * Get variable from request
     * This function uses 'dot' notation to access values from array
     * 
     * @param type $name
     * @param type $default
     * @return type
     */
    public static function get($name, $default = null)
    {
        if(empty($name))
        {
            return $_SERVER;
        }
        
        if(strpos($name, ".") !== false)
        {
            $array = $_SERVER;
            $path = explode(".", $name);
            foreach($path as $element)
            {
                if(!is_array($array) || !isset($array[$element])) {
                    return $default;
                }
                
                $array = $array[$element];
            }
            
            return $array;
        }
        
        return $_SERVER[$name];
    }

    public static function getWhmcsCurrentURL()
    {
        $repo       = new WhmcsConfiguration();
        $whmcsUrl   = $repo->getSetting("SystemURL");
        $whmcsUrl   = trim($whmcsUrl, '/').'/';

        return $whmcsUrl;
    }

    public static function getWhmcsDomain()
    {
        $whmcsUrl   = self::getWhmcsCurrentURL();
        $url        = parse_url($whmcsUrl);

        return $url["host"];
    }
    
    public static function getCurrentURL($query = array())
    {
        $scheme = self::get("HTTPS") ? "https" : "http";
        $domain = self::get(Configuration::getCGIHostnameVariableName());
        
        $path = self::get("SCRIPT_NAME");
        $path = trim($path, "/");
                
        $query = !empty($query) ? "?".http_build_query($query) : "";

        
        $url = "{$scheme}://{$domain}/{$path}{$query}";
        return $url;
    }
    
    public static function getCurrentSystemURL()
    {
        $scheme = self::get("HTTPS") ? "https" : "http";
        $domain = self::get(Configuration::getCGIHostnameVariableName());
        $path = str_replace(basename(self::get("SCRIPT_NAME")), "", self::get("SCRIPT_NAME"));
        
        $url = "{$scheme}://{$domain}{$path}";
        return $url;
    }
    
    public static function getSystemURL($domain)
    {
        $scheme = self::get("HTTPS") ? "https" : "http";
        $path = str_replace(basename(self::get("SCRIPT_NAME")), "", self::get("SCRIPT_NAME"));
        
        $url = "{$scheme}://{$domain}{$path}";
        return $url;
    }
    
    /**
     * Discard the contents of the output buffer
     * 
     */
    public static function obClean()
    {
        $obStatus = ob_get_status(); /*check if ourput buffering enabled in php.ini*/
        if($obStatus['level'] > 0)
        {
            if (ob_get_level() > 0)
            {
                ob_clean();
            }
            else
            {
                ob_start();
                ob_clean();
            }
        }
    }

    /**
     * Check if the current script is run by cron
     *
     * @return bool
     */
    public static function isRunByCron()
    {
        //If $_SERVER varaiable is empty check if we are in CLI
        if(empty(Server::get("SCRIPT_NAME")))
        {
            $isCron = in_array(php_sapi_name(), ['cli']) || empty(Server::get("REMOTE_ADDR"));
        }
        else
        {
            $isCron = (basename(Server::get("SCRIPT_NAME")) == "cron.php");
        }

        return $isCron;

    }

    public static function getDomainWithoutWwwPrefix($domain): string
    {
        $trimmedDomain = trim($domain, ' ');
        $parsedDomain = parse_url($trimmedDomain);
        $newDomain =  $parsedDomain['host'] ?: trim($parsedDomain['path'], '/');
        return str_replace('www.', '', $newDomain);
    }
}

