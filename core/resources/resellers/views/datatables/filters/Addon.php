<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Views\Datatables\Filters;

use \MGModule\ResellersCenter\Core\Datatable\Filters\Addon as AddonFilter;
use MGModule\ResellersCenter\Core\Traits\IsResellerProperty;
use MGModule\ResellersCenter\Repository\Whmcs\Addons;

/**
 * Description of Addon
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Addon extends AddonFilter
{
    use IsResellerProperty;

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