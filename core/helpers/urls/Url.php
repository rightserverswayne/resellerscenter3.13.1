<?php

namespace MGModule\ResellersCenter\Core\Helpers\Urls;

use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Server;

/**
 * Description of Url
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Url
{
    const KNOWLEDGEBASE = 1;

    const ANNOUNCEMENTS = 2;

    const DOWNLOADS     = 3;

    const CART          = 4;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $config;

    /**
     * Route mode from WHMCS configuration
     *
     * @var string
     */
    protected $mode;

    /**
     * @var mixed
     */
    protected $parsed;

    /**
     * Url constructor.
     *
     * @param $url
     */
    public function __construct($url = null)
    {
        $this->url      = $url ?: $this->getCurrentUrl();
        $this->config   = $this->getConfig();
        $this->mode     = Whmcs::getConfig("RouteUriPathMode");
        $this->parsed   = [];
    }

    public static function isOnPage($type)
    {
        $url = new Url();
        return $url->is($type);
    }

    /**
     * Set type of the url
     */
    public function is($type)
    {
        $this->type = $type;
        $this->process();

        return $this->parsed["valid"];
    }

    /**
     *
     */
    protected function process()
    {
        $urlRegex       = $this->config[$this->type]["regexp"][$this->mode]["url"];
        $paramsRegex    = $this->config[$this->type]["regexp"][$this->mode]["params"];

        $this->parsed["valid"] = preg_match("/{$urlRegex}/", $this->url);
        preg_match("/{$paramsRegex}/", $this->url, $this->parsed["params"]);
    }

    /**
     * Load config from file
     *
     * @return mixed
     */
    protected function getConfig()
    {
        $data = require __DIR__ . DS . "Config.php";

        return $data;
    }

    /**
     * Get current URL
     *
     * @return string
     */
    protected function getCurrentUrl()
    {
        $scheme     = Server::get("REQUEST_SCHEME");
        $domain     = Server::get("HTTP_HOST") ?: Server::get("SERVER_NAME");
        $request    = Server::get("REQUEST_URI");

        return "{$scheme}://{$domain}{$request}";
    }
}