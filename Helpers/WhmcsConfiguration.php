<?php

namespace MGModule\ResellersCenter\Helpers;

use MGModule\ResellersCenter\models\whmcs\Configuration;

class WhmcsConfiguration
{
    const URI_MODE_OVERRIDE         = 'UriModeOverride';
    const URI_REWRITE_AUTO_MANAGER  = 'UriRewriteAutoManage';
    const ROUTE_URI_PATH            = 'RouteUriPathMode';

    const BASIC_URLS                = 'basic';
    const FRIENDLY_INDEX            = 'acceptpathinfo';
    const FULL_FRIENDLY_REWRITE     = 'rewrite';

    public static function getValue($setting)
    {
        return Configuration::where('setting', $setting)->first()->value;
    }

}