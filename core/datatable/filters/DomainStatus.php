<?php
namespace MGModule\ResellersCenter\Core\Datatable\Filters;
use MGModule\ResellersCenter\Core\Datatable\AbstractFilter;
use MGModule\ResellersCenter\mgLibs\Lang;

/**
 * Description of DomainStatus
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class DomainStatus extends AbstractFilter
{
    public function __construct()
    {
        $this->data =
        [
            [
                "id"    => "Pending",
                "text"  => Lang::absoluteT('datatable','filters','domain','status', 'options', 'pending')
            ],
            [
                "id"    => "Pending Registration",
                "text"  => Lang::absoluteT('datatable','filters','domain','status', 'options', 'pendingregistration')
            ],
            [
                "id"    => "Pending Transfer",
                "text"  => Lang::absoluteT('datatable','filters','domain','status', 'options', 'pendingtransfer')
            ],
            [
                "id"    => "Active",
                "text"  => Lang::absoluteT('datatable','filters','domain','status', 'options', 'active')
            ],
            [
                "id"    => "Grace",
                "text"  => Lang::absoluteT('datatable','filters','domain','status', 'options', 'grace')
            ],
            [
                "id"    => "Redemption",
                "text"  => Lang::absoluteT('datatable','filters','domain','status', 'options', 'redemption')
            ],
            [
                "id"    => "Expired",
                "text"  => Lang::absoluteT('datatable','filters','domain','status', 'options', 'expired')
            ],
            [
                "id"    => "Transferred Away",
                "text"  => Lang::absoluteT('datatable','filters','domain','status', 'options', 'transferredaway')
            ],
            [
                "id"    => "Cancelled",
                "text"  => Lang::absoluteT('datatable','filters','domain','status', 'options', 'cancelled')
            ],
            [
                "id"    => "Fraud",
                "text"  => Lang::absoluteT('datatable','filters','domain','status', 'options', 'fraud')
            ],
        ];
    }
}