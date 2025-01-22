<?php

namespace MGModule\ResellersCenter\Core\Helpers;

use MGModule\ResellersCenter\Addon;

class AdminAreaHelper
{
    const JS_CONTROLLERS =
        [
            "Resellers.js",
        ];

    public static function getJavaScriptControllers()
    {
        $dir            = Addon::getMainDIR().DS."templates".DS."admin".DS."controllers".DS;
        $controllers    = self::JS_CONTROLLERS;

        $result = "";
        foreach ($controllers as $controller) {
            $content = file_exists($dir.$controller) ? file_get_contents($dir.$controller) : '';
            $result .= "<script type='text/javascript'>{$content}</script>";
        }

        return $result;
    }

}