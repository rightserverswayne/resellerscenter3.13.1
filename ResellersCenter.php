<?php

if(!defined('DS'))define('DS',DIRECTORY_SEPARATOR);

function ResellersCenter_config(){
    require_once __DIR__.DS.'Loader.php';
    new \MGModule\ResellersCenter\Loader();
    return MGModule\ResellersCenter\Addon::config();
}

function ResellersCenter_activate(){
    require_once __DIR__.DS.'Loader.php';
    new \MGModule\ResellersCenter\Loader();
    return MGModule\ResellersCenter\Addon::activate();
}

function ResellersCenter_deactivate(){
    require_once __DIR__.DS.'Loader.php';
    new \MGModule\ResellersCenter\Loader();
    return MGModule\ResellersCenter\Addon::deactivate();
}

function ResellersCenter_upgrade($vars){
    require_once __DIR__.DS.'Loader.php';
    new \MGModule\ResellersCenter\Loader();
    return MGModule\ResellersCenter\Addon::upgrade($vars);
}

function ResellersCenter_output($params){
    require_once __DIR__.DS.'Loader.php';
    new \MGModule\ResellersCenter\Loader();
    
    MGModule\ResellersCenter\Addon::I(FALSE,$params);
    
    if(!empty($_REQUEST['json']))
    {
        ob_clean();
        header('Content-Type: text/plain');
        echo MGModule\ResellersCenter\Addon::getJSONAdminPage($_REQUEST);
        die();
    }
    elseif(!empty($_REQUEST['customPage']))
    {
        ob_clean();
        echo MGModule\ResellersCenter\Addon::getHTMLAdminCustomPage($_REQUEST);
        die();
    }
    else
    {
        echo MGModule\ResellersCenter\Addon::getHTMLAdminPage($_REQUEST);
    }
}


function ResellersCenter_clientarea(){
    require_once __DIR__.DS.'Loader.php';
    new \MGModule\ResellersCenter\Loader();
    
    if(!empty($_REQUEST['json']))
    {
        ob_clean();
        header('Content-Type: text/plain');
        echo MGModule\ResellersCenter\Addon::getJSONClientAreaPage($_REQUEST);
        die();
    }
    else
    {
         return MGModule\ResellersCenter\Addon::getHTMLClientAreaPage($_REQUEST);
    }
}

