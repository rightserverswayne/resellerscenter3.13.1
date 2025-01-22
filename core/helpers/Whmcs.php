<?php
namespace MGModule\ResellersCenter\Core\Helpers;
use MGModule\ResellersCenter\Loader;
use MGModule\ResellersCenter\repository\whmcs\PaymentGateways;

/**
 * Description of Whmcs
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Whmcs
{
    public static function getConfig($name)
    {
        global $whmcs;
        return $whmcs->get_config($name);
    }

    public static function lang($name, $params = [])
    {
        if(strpos($name, ".") !== false)
        {
            $path = explode(".", $name);
        }

        global $whmcs;
        if(is_array($path))
        {
            $lang = $whmcs->get_lang($path[0]);
            unset($path[0]);

            foreach($path as $element)
            {
                $lang = $lang[$element];
            }
        }
        else
        {
            $lang = $whmcs->get_lang($name);
        }

        //fetch variables
        foreach($params as $key => $value)
        {
            $lang = str_replace(":{$key}", $value, $lang);
        }

        return $lang;
    }


    /**
     * Get countries table from <WHMCS_PATH>/include/countries.php
     * If on WHMCS7 parse JSON data from <WHMCS_PATH>/resources/country/dist.countries.json
     *
     * @return null
     * @throws \Exception
     */
    public static function getCountries()
    {
        $countries = null;
        if(self::isVersion("7.0.0"))
        {
            $data = file_get_contents(Loader::$whmcsDir.DS.'resources'.DS.'country'.DS.'dist.countries.json');
            $parsed = json_decode($data);
            foreach($parsed as $code => $country)
            {
                $countries[$code] = $country->name;
            }
        }
        else
        {
            require Loader::$whmcsDir.DS.'includes'.DS.'countries.php';

            if(!is_array($countries))
            {
                throw new \Exception('Unable to find countries list');
            }
        }

        return $countries;
    }

    /**
     * Get all existing templates in WHMCS system
     *
     * @since 3.0.0
     * @return type
     */
    public static function getAvailableTemplates()
    {
        $templates = scandir(Loader::$whmcsDir . DS . 'templates');
        $templates = array_diff($templates, array ('.', '..', 'orderforms', 'index.php'));

        //Just in case...
        foreach($templates as $key => $file)
        {
            //We need only directories
            if(is_file(Loader::$whmcsDir . DS . $file))
            {
                unset($templates[$key]);
            }
        }

        return $templates;
    }

    /**
     * Get existing order templates from WHMCS system
     *
     * @since 3.0.0
     * @return type
     */
    public static function getAvailableOrderTemplates()
    {
        $templates = scandir(Loader::$whmcsDir . DS . 'templates'. DS .'orderforms');
        $templates = array_diff($templates, array('.', '..', 'index.php'));

        return $templates;
    }

    /**
     * Get all available languages from WHMCS
     *
     * @return type
     */
    public static function getAllLanguages()
    {
        $languages = array();
        if ($handle = opendir(ROOTDIR.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR))
        {
            while(false !== ($entry = readdir($handle)))
            {
                $pos = strpos($entry,'.php');
                if($pos !== false && strpos($entry, 'index') === false)
                {
                    $languages[] = ucfirst(substr($entry,0,$pos));
                }
            }

            closedir($handle);
        }

        return $languages;
    }

    public static function getFirstAvailableGateway($exclude = [])
    {
        $repo = new PaymentGateways();
        $gateways = $repo->getEnabledGatewaysArray();

        foreach($gateways as $gatewayName => $gatewayDetails)
        {
            if(!in_array($gatewayName, $exclude))
            {
                $gatewayDetails["sysname"] = $gatewayName;
                return $gatewayDetails;
            }
        }

        return [];
    }

    /**
     * Check if module is running in WHMCS7
     *
     * @global type $whmcs
     * @return boolean
     */
    public static function isVersion($version)
    {
        global $whmcs;
        if(version_compare($whmcs->getVersion()->getRelease(), $version, ">="))
        {
            return true;
        }

        return false;
    }
}