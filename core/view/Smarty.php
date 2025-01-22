<?php

namespace MGModule\ResellersCenter\Core\View;

/**
 * Description of Smarty.php
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Smarty extends \WHMCS\Smarty
{
    public function __construct()
    {
        parent::__construct();
        $this->prepare();
    }

    protected function prepare()
    {
        global $templates_compiledir;

        $this->setCompileDir($templates_compiledir);
        $this->setTemplateDir(ROOTDIR.DS."templates");
    }
}
