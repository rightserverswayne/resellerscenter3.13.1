<?php
namespace MGModule\ResellersCenter\Core\Datatable\Filters;
use MGModule\ResellersCenter\Core\Datatable\AbstractFilter;
use MGModule\ResellersCenter\repository\whmcs\PaymentGateways;

/**
 * Description of PaymentMethod
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class PaymentMethod extends AbstractFilter
{
    public function getData($search = "")
    {
        $repo       = new PaymentGateways();
        $gateways   = $repo->getEnabledGatewaysArray($search);

        $result = [];
        foreach($gateways as $gateway => $settings)
        {
            $result[] =
            [
                "id" => $gateway,
                "text" => $settings["name"]
            ];
        }

        return $result;
    }
}