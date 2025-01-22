<?php
namespace MGModule\ResellersCenter\Core\Helpers;

/**
 * Description of DataTable
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class DataTable
{
    public static function getFilter($name)
    {
        $namespace  = "\\MGModule\\ResellersCenter\\Core\\Datatable\\Filters\\";
        $classname  = "{$namespace}{$name}";

        $filter     = new $classname();
        return $filter;
    }
}