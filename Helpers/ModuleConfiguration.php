<?php

namespace MGModule\ResellersCenter\Helpers;

use MGModule\ResellersCenter\Core\Helpers\Files;

class ModuleConfiguration
{
    public static function getModuleConfiguration()
    {
        $path = dirname(__DIR__) .DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.json';
        return json_decode(Files::getFileData($path), true);
    }
}