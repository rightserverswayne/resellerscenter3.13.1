<?php
namespace MGModule\ResellersCenter\core;
use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;

/**
 * Description of WHMCSRequest
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class WHMCSRequest 
{
    public $curl;
    
    public $whmcsURL;
    
    public $type;
    
    public $cookiesPath;
    
    /**
     * Initialize cURL and set WHMCS system URL.
     * Object will always use same cURL session.
     * 
     * @global type $CONFIG
     * @param type $type
     */
    public function __construct($type = 'clientarea') 
    {
        //init cURL
        $this->curl = curl_init();
        $this->cookiesPath = $this->generateCookiesFile();
        
        //set type
        $this->type = $type;
       
        //Get WHMCS URL
        global $CONFIG;
        if(Whmcs::isVersion("7.0.0"))
        {
            $this->whmcsURL = $CONFIG["SystemURL"];
        }
        else
        {
            if($CONFIG["SystemSSLURL"] != '') {
                $this->whmcsURL = $CONFIG["SystemSSLURL"];
            }
            else {
                $this->whmcsURL = $CONFIG["SystemURL"];
            }
        }
    }
    
    /**
     * Get CRSF token from HTML response.
     * 
     * @return type
     */
    public function getToken()
    {
        if($this->type == 'clientarea') {
            $url = $this->whmcsURL."/index.php";
        }
        else 
        {
            include ROOTDIR."configuration.php";
            $path = empty($customadminpath) ? "admin" : $customadminpath;
            $url = $this->whmcsURL."/".$path."/index.php";
        }

        //Get HTML response and find token input
        $content = $this->makeRequest($url, array());
        $pos = strpos($content, '<input type="hidden" name="token" value="');
        
        $token = substr($content, $pos+41, 40);
        return $token;
    }
    
    /**
     * Make request
     * 
     * @param type $url
     * @param type $query
     * @return type
     */
    public function makeRequest($url, $query)
    {
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
        
        curl_setopt($this->curl, CURLOPT_REFERER, $_SERVER['REQUEST_URI']);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookiesPath);
        
        //Set params
        $params = http_build_query($query);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $params);
        
        return curl_exec($this->curl);
    }
    
    private function generateCookiesFile()
    {
        $addon = ADDON_DIR.DS."storage".DS."cookies";
        $this->cookiesPath = $addon . DS . uniqid("cookie_");
    }
    
//    public function authenticateClient($username, $password)
//    {
//        //Login page URL
//        $url = $this->whmcsURL."/dologin.php";
//        $this->makeRequest($url, array("username" => $username, "password" => $password));
//    }
}