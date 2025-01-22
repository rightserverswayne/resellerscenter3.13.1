<?php
namespace MGModule\ResellersCenter\Core\Datatable\Filters;

use MGModule\ResellersCenter\Core\Datatable\AbstractFilter;
use MGModule\ResellersCenter\Repository\Whmcs\Addons;

/**
 * Description of Addon
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Addon extends AbstractFilter
{
    public function getData($search = "")
    {
        $repo   = new Addons();
        $addons = $repo->getAvailable($search);

        $result = [];
        foreach ($addons as $addon)
        {
            $result[] =
            [
                "id"    => $addon->id,
                "text"  => $addon->name,
            ];
        }

        return $result;
    }
}