<?php

namespace MGModule\ResellersCenter\mgLibs\Helpers;

use MGModule\ResellersCenter\Core\Configuration;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\models\whmcs\ProductSlug;

class UrlGenerator
{
    const INDEX_PHP = "index.php";
    const STORE = "store";
    const RESID = "resid";

    public $reseller;

    public function __construct()
    {
        $this->reseller = Reseller::getLogged();
    }

    public function generateFriendlyUrlForProductById($productId)
    {
        $productSlugs = $this->getProductSlugs($productId);

        $url =  $this->getStoreUrl().DS;
        $url .= $productSlugs->group_slug.DS;
        $url .= $productSlugs->slug;

        return $this->reseller->settings->private->domain ? $url : $this->addResidParameter($url);
    }

    public function generateFriendlyUrlForProductGroupByProductId($productId)
    {
        $productSlugs = $this->getProductSlugs($productId);

        $url = $this->getStoreUrl().DS;
        $url .= $productSlugs->group_slug;

        return $this->reseller->settings->private->domain ? $url : $this->addResidParameter($url);
    }

    private function getProductSlugs($productId)
    {
        $productSlugs = ProductSlug::where('product_id', $productId)->first();
        return $productSlugs;
    }

    private function getBaseHostUrl()
    {
        $resellerDomain = $this->reseller->settings->private->domain;
        $domain = $resellerDomain ?: Server::get(Configuration::getCGIHostnameVariableName());
        return Server::getSystemURL($domain);
    }

    private function getStoreUrl()
    {
        $url = $this->getBaseHostUrl();
        $url .= self::INDEX_PHP.DS.self::STORE;
        return $url;
    }

    private function addResidParameter($url)
    {
        return $url.'?'.self::RESID.'='.$this->reseller->id;
    }

}
