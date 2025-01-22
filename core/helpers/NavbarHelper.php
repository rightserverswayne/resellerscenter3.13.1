<?php

namespace MGModule\ResellersCenter\Core\Helpers;

/*
File:   NavbarHelper.php
Date:   05.05.2020
Author: Tomasz Bielecki (tomasz.bi@modulesgarden.com)
Class NavbarHelper
*/

use MGModule\ResellersCenter\mgLibs\Lang;

class NavbarHelper
{
    const WHMCS_GROUPS_MENU_KEY = "Store";

    public static function getResellerAreaButtonDetails()
    {
        $path = dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'customs';
        $data = json_decode(Files::getFileData($path.DIRECTORY_SEPARATOR.'resellerAreaButton.json'), true);

        $defaultOptions = ['name' => 'ResellersCenter','icon' => 'fas fa-calendar-alt', 'label' => Lang::absoluteT('addonCA','resellerarea'), 'uri' => 'index.php?m=ResellersCenter','order' => 99,];;

        $id = $data['id'] ? $data['id'] : 'uniqueMenuItemName_ResellerCenter';
        $options = $data['options'] ? $data['options'] : $defaultOptions;

        $options['label'] = $options['rawLabel'] ? $options['rawLabel'] : Lang::absoluteT('addonCA',$options['label']);

        return [$id, $options];
    }

}