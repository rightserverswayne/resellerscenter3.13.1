<?php
namespace MGModule\ResellersCenter\Core\Helpers;

/**
 * Description of CartDomain
 *
 * @author Paweł Złamaniec
 */
class Files
{
    /**
     * @param mixed ...$path
     * @return mixed|string
     */
    public static function getPath(...$path)
    {
        $result = self::getAddonPath();
        foreach($path as $dir)
        {
            $result .= DS.$dir;
        }

        return $result;
    }

    /**
     * @param mixed ...$path
     * @return mixed|string
     */
    public static function getWhmcsPath(...$path)
    {
        $result = str_replace(DS."modules".DS."addons".DS."ResellersCenter", "",  self::getAddonPath());
        foreach($path as $dir)
        {
            $result .= DS.$dir;
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public static function getAddonPath()
    {
        $path = str_replace(DS."core".DS."helpers", "",  __DIR__);
        return $path;
    }

    /**
     * @param $file
     * @return false|string|null
     */
    public static function getFileData($file)
    {
        if(!file_exists($file))
        {
            return null;
        }

        return file_get_contents($file);
    }
}
