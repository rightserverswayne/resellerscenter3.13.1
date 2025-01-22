<?php
namespace MGModule\ResellersCenter\Core\View\Templates;

use MGModule\ResellersCenter\Core\View\Smarty;
use MGModule\ResellersCenter\Loader;

/**
 * Description of Template
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Template
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function parseSingleTemplate($name, $params)
    {
        $smarty = new Smarty();
        $smarty->setTemplateDir($this->getTemplateDir());

        foreach($params as $key => $value)
        {
            $smarty->assign($key, $value);
        }

        return $smarty->fetch("{$name}.tpl");
    }

    public function getTemplateDir()
    {
        $result = Loader::$whmcsDir."templates";
        if($this instanceof OrderTemplate)
        {
            $result .= DS."orderforms";
        }

        return $result.DS.$this->name.DS;
    }

    protected function getFiles()
    {
        $dir = $this->getTemplateDir();

        $files = scandir($dir);
        $files = array_diff($files, ['.', '..']);

        return $files;
    }
}