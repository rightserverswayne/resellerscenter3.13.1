<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Views;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;

use MGModule\ResellersCenter\Core\Resources\Resellers\Views\Datatables\Filters;
use MGModule\ResellersCenter\Core\Traits\HasProperties;

/**
 * Description of Datatable
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Datatable
{
    use HasProperties;

    /**
     * Reseller Object
     *
     * @var Reseller
     */
    protected $reseller;

    /**
     * Filters Object
     *
     * @var Filters
     */
    protected $filters;

    /**
     * Datatable constructor.
     * @param Reseller $reseller
     */
    public function __construct(Reseller $reseller)
    {
        $this->reseller = $reseller;
    }

    /**
     * Override properties classes
     *
     * @return array
     */
    protected function getOverriddenPropertiesClasses()
    {
        return
        [
            "filters" =>
            [
                "class"  => Filters::class,
                "parent" => $this->reseller,
            ]
        ];
    }
}