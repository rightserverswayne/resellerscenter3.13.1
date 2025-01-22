<?php

namespace MGModule\ResellersCenter\Core\Helpers\Urls;
use MGModule\ResellersCenter\Core\Helpers\Whmcs as WHMCSHelper;

class FriendlyUrl
{

    // friendly url options
    const BASIC_URL         = 'index.php?rp=/';
    const FULL_REWRITE      = '';
    const FRIENDLY_INDEX    = 'index.php/';


    public static function generate()
    {
        // get friendly url config from whmcs settings
        $urlConfig = WHMCSHelper::getConfig("RouteUriPathMode");
        switch($urlConfig)
        {
            case 'basic': // basic
                $url = self::BASIC_URL;
                break;
            case 'rewrite': // full rewrite
                $url = self::FULL_REWRITE;
                break;
            case 'acceptpathinfo': // friendly index
                $url = self::FRIENDLY_INDEX;
                break;
        }

        return $url;
    }
}