<?php

namespace MGModule\ResellersCenter\Core\Datatable\Filters;
use MGModule\ResellersCenter\Core\Datatable\AbstractFilter;
use MGModule\ResellersCenter\mgLibs\Lang;

/**
 * Description of ProductType
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ProductType extends AbstractFilter
{
    public function __construct()
    {
        $this->data =
        [
            [
                "id"    => "hostingaccount",
                "text"  => Lang::absoluteT('datatable','filters','hosting','type', 'options', 'hostingaccount')
            ],
            [
                "id"    => "reselleraccount",
                "text"  => Lang::absoluteT('datatable','filters','hosting','type', 'options', 'reselleraccount')
            ],
            [
                "id"    => "server",
                "text"  => Lang::absoluteT('datatable','filters','hosting','type', 'options', 'server')
            ],
            [
                "id"    => "other",
                "text"  => Lang::absoluteT('datatable','filters','hosting','type', 'options', 'other')
            ]
        ];
    }
}