<?php
namespace MGModule\ResellersCenter\Controllers\Addon\Admin;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;
use MGModule\ResellersCenter\repository\whmcs\Configuration;

/**
 * Description of Dashboard
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Dashboard extends AbstractController
{
    public function indexHTML()
    {        
        return array(
            'tpl'   => 'base',
            'vars' => array()
        );
    }
    
    public function setSkipDashboardJSON()
    {
        $configuration = new Configuration();
        $configuration->saveSetting("RC_Skip_Dashboard", 1);
    }
}