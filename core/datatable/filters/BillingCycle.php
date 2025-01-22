<?php
namespace MGModule\ResellersCenter\Core\Datatable\Filters;
use MGModule\ResellersCenter\Core\Datatable\AbstractFilter;
use MGModule\ResellersCenter\mgLibs\Lang;

/**
 * Description of BillingCycle
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class BillingCycle extends AbstractFilter
{
    public function __construct()
    {
        $this->data =
        [
            [
                "id"    => "Free Account",
                "text"  => Lang::absoluteT('datatable','filters','hosting','billingcycle', 'options', 'free')
            ],
            [
                "id"    => "One Time",
                "text"  => Lang::absoluteT('datatable','filters','hosting','billingcycle', 'options', 'onetime')
            ],
            [
                "id"    => "Monthly",
                "text"  => Lang::absoluteT('datatable','filters','hosting','billingcycle', 'options', 'monthly')
            ],
            [
                "id"    => "Quarterly",
                "text"  => Lang::absoluteT('datatable','filters','hosting','billingcycle', 'options', 'quarterly')
            ],
            [
                "id"    => "Semi-Annually",
                "text"  => Lang::absoluteT('datatable','filters','hosting','billingcycle', 'options', 'semiannually')
            ],
            [
                "id"    => "Annually",
                "text"  => Lang::absoluteT('datatable','filters','hosting','billingcycle', 'options', 'annually')
            ],
            [
                "id"    => "Biennially",
                "text"  => Lang::absoluteT('datatable','filters','hosting','billingcycle', 'options', 'biennially')
            ],
            [
                "id"    => "Triennially",
                "text"  => Lang::absoluteT('datatable','filters','hosting','billingcycle', 'options', 'triennially')
            ],
        ];
    }
}