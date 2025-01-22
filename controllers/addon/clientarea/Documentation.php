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
use MGModule\ResellersCenter\mgLibs\process\AbstractController;

use MGModule\ResellersCenter\repository\Documentations;
use MGModule\ResellersCenter\Core\Helpers\Reseller;

/**
 * Description of documentation
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Documentation extends AbstractController
{
    public function indexHTML()
    {
        $reseller = Reseller::getLogged();
        $reseller->settings->admin->documentation;
        
        $repo = new Documentations();
        $documentation = $repo->find($reseller->settings->admin->documentation);
        
        //Save information that client already saw this page and do not dispaly it after login
        if(! $reseller->settings->private->docsDoNotShowAgain)
        {
            $reseller->settings->private->docsDoNotShowAgain = 1;
            $reseller->settings->private->save();
        }

        return array(
            'tpl'   => 'base',
            'vars' => array(
                "documentation" => $documentation
            )
        );
    }
}
