<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Products\domains\json;

/**
 * Description of ResponseBase
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
abstract class Base
{
    /**
     * Domain query string from cart
     *
     * @var string
     */
    protected $search;
    
    /**
     * Domain object
     *
     * @var \MGModule\ResellersCenter\Core\Whmcs\Products\domains\domain
     */
    protected $domain;

    /**
     * Init
     * 
     * @param \MGModule\ResellersCenter\Core\Whmcs\Products\Domain\Domain $domain
     * @param type $search
     */
    public function __construct(\MGModule\ResellersCenter\Core\Whmcs\Products\domains\Domain $domain, $search)
    {
        if (!function_exists("getTLDPriceList"))
        {
            require(ROOTDIR . "/includes/domainfunctions.php");
        }
        
        $this->domain = $domain;
        $this->search = $search;
    }
    
    /**
     * Get standard cart response as array in JSON format
     */
    abstract public function getResponse();
    
    /**
     * Get WHMCS domain search object
     * 
     * @param type $search
     * @return \WHMCS\Domains\Domain
     */
    protected function getWhmcsDomain()
    {
        $search = (strpos($this->search, ".") !== false) ? $this->search : "{$this->search}{$this->domain->extension}";
        
        $domain = \WHMCS\Input\Sanitize::decode($search);
        $domain = \WHMCS\Config\Setting::getValue("AllowIDNDomains") ? mb_strtolower($domain) : strtolower($domain);
        $domain = str_replace(array("'", "+", ",", "|", "!", "\\", "\"", "£", "\$", "%", "&", "/", "(", ")", "=", "?", "^", "*", " ", "°", "§", ";", ":", "_", "<", ">", "]", "[", "@", ")"), "", $domain);
        $domain = new \WHMCS\Domains\Domain($domain);
        
        return $domain;
    }
}