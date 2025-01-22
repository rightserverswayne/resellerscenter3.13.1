<?php
namespace MGModule\ResellersCenter\Core\Datatable\Filters;
use MGModule\ResellersCenter\Core\Datatable\AbstractFilter;
use MGModule\ResellersCenter\mgLibs\Lang;

/**
 * Description of Status
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Status extends AbstractFilter
{
    public function __construct()
    {
        $this->data =
        [
            [
                "id"    => "Pending",
                "text"  => Lang::absoluteT('datatable','filters','hosting','status', 'options', 'pending')
            ],
            [
                "id"    => "Active",
                "text"  => Lang::absoluteT('datatable','filters','hosting','status', 'options', 'active')
            ],
            [
                "id"    => "Completed",
                "text"  => Lang::absoluteT('datatable','filters','hosting','status', 'options', 'completed')
            ],
            [
                "id"    => "Suspended",
                "text"  => Lang::absoluteT('datatable','filters','hosting','status', 'options', 'suspended')
            ],
            [
                "id"    => "Terminated",
                "text"  => Lang::absoluteT('datatable','filters','hosting','status', 'options', 'terminated')
            ],
            [
                "id"    => "Cancelled",
                "text"  => Lang::absoluteT('datatable','filters','hosting','status', 'options', 'Cancelled')
            ],
            [
                "id"    => "Fraud",
                "text"  => Lang::absoluteT('datatable','filters','hosting','status', 'options', 'Fraud')
            ],
        ];
    }
}