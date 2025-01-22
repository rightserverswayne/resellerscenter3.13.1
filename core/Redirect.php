<?php
namespace MGModule\ResellersCenter\Core;

/**
 * Description of Redirect
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Redirect 
{
    /**
     * This is just a shortcut - we don't need to create new object each time
     * 
     * @param type $page
     */
    public static function to($domain, $page, $query = [], $scheme = "")
    {
        if(!$scheme)
        {
            $scheme = $_SERVER["HTTPS"] == 'on' ? "https" : "http";
        }

        new Redirect($scheme, $domain, $page, $query);
    }
    
    public static function query($query = [])
    {
        $scheme = $_SERVER["HTTPS"] == 'on' ? "https" : "http";
        $domain = $_SERVER["HTTP_HOST"];
        $path = $_SERVER["SCRIPT_NAME"];
        
        new Redirect($scheme, $domain, $path, $query);
    }
    
    public static function toPageWithQuery($page, $query = [])
    {
        $scheme = $_SERVER["HTTPS"] == 'on' ? "https" : "http";
        $domain = $_SERVER["HTTP_HOST"];
        $path = str_replace(basename($_SERVER["SCRIPT_NAME"]), "", $_SERVER["SCRIPT_NAME"]) . $page;
        
        new Redirect($scheme, $domain, $path, $query);
    }
    
    public static function refresh()
    {
        header("Refresh:0");
        die();
    }
    
    /**
     * Make new redirection
     * 
     * @param type $scheme 
     * @param type $domain 
     * @param string $page
     * @param array $query
     * @param int $code
     */
    public function __construct($scheme, $domain, $page = "", $query = [], $code = 302) 
    {
        //Parse query
        if(is_array($query))
        {
            $query = http_build_query($query);
            $query = urldecode($query);
        }

        //Check if scheme is not in domain already
        $scheme = strpos($domain, "http") === 0 ? "" : "{$scheme}://";

        //Create url
        $url  = $scheme . rtrim($domain, "/") . "/" . trim($page, "/");
        $url .= $query ? "?{$query}": "";

        $this->process($url, $code);
    }
        
    private function process($url, $code)
    {
        http_response_code($code);
        header('Location: ' . $url);
        exit(); 
    }
}
