<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Views\Datatables;

use MGModule\ResellersCenter\Core\Traits\IsResellerProperty;

/**
 * Description of Filters
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Filters
{
    use IsResellerProperty;

    /**
     * Get filter object
     *
     * @param $type
     * @return mixed
     */
    public function get($type)
    {
        $namespace  = "\\MGModule\\ResellersCenter\\Core\\Resources\\Resellers\\Views\\Datatables\\Filters\\";
        $classname  = "{$namespace}{$type}";

        $filter     = new $classname($this->reseller);
        return $filter;
    }
}