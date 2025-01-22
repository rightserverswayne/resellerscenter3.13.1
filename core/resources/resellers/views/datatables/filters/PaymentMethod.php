<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Views\Datatables\Filters;

use \MGModule\ResellersCenter\Core\Datatable\Filters\PaymentMethod as PaymentMethodFilter;
use MGModule\ResellersCenter\Core\Traits\IsResellerProperty;

/**
 * Description of PaymentMethod
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class PaymentMethod extends PaymentMethodFilter
{
    use IsResellerProperty;

    /**
     * Get the filter data
     *
     * @param string $search
     * @return array
     */
    public function getData($search = "")
    {
        $result     = [];
        $gateways   = $this->reseller->gateways->getAvailable();

        foreach($gateways as $gateway => $settings)
        {
            if(strpos($settings["name"], $search) === false && !empty($search))
            {
                continue;
            }

            $result[] =
            [
                "id"    => $settings["sysname"],
                "text"  => $settings["name"]
            ];
        }

        return $result;
    }
}