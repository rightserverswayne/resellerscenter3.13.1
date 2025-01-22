<?php
namespace MGModule\ResellersCenter\Core;

/**
 * Description of Configuration.php
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Configuration
{
    const FILE_LOCATION = ROOTDIR.DS."modules".DS."addons".DS."ResellersCenter".DS."config".DS."configuration.php";
    const SAMPLE_FILE_LOCATION = ROOTDIR.DS."modules".DS."addons".DS."ResellersCenter".DS."config".DS."configuration.php_new";
    
    private $config = [];

    public static function __callStatic($name, $params)
    {
        $variable = substr($name, 3);

        $configObj = new Configuration();
        $result = $configObj->config[$variable];

        if(empty($result))
        {
            throw new \Exception("Variable `{$variable}` has not been found in Resellers Center configuration file");
        }

        return $result;
    }

    public function __construct()
    {
        $this->config = require file_exists(self::FILE_LOCATION) ? self::FILE_LOCATION : self::SAMPLE_FILE_LOCATION;
    }
}