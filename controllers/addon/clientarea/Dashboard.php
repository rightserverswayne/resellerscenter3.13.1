<?php

/* * ********************************************************************
 * MGMF product developed. (2016-02-23)
 * *
 *
 *  CREATED BY MODULESGARDEN       ->       http://modulesgarden.com
 *  CONTACT                        ->       contact@modulesgarden.com
 *
 *
 * This software is furnished under a license and may be used and copied
 * only  in  accordance  with  the  terms  of such  license and with the
 * inclusion of the above copyright notice.  This software  or any other
 * copies thereof may not be provided or otherwise made available to any
 * other person.  No title to and  ownership of the  software is  hereby
 * transferred.
 *
 *
 * ******************************************************************** */

namespace MGModule\ResellersCenter\controllers\addon\clientarea;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;

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
        $reseller = Reseller::getLogged();
        
        $settings = new \MGModule\ResellersCenter\repository\ResellersSettings();
        $settings->saveSingleSetting($reseller->id, "skipResellerDashboard", 1, 1);
        
        return array("saved" => 1);
    }
}