<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers;

use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Traits\IsResellerProperty;
use MGModule\ResellersCenter\Core\Whmcs\Gateways\PaymentGateway;

/**
 * Description of Gateways
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Gateways
{
    use IsResellerProperty;

    /**
     * Get available payment gateways in reseller store
     *
     * @return array
     */
    public function getAvailable()
    {
        if($this->reseller->settings->admin->resellerInvoice)
        {
            $result = [];
            $gateways = Helper::getCustomGateways($this->reseller->id);

            foreach($gateways as $gateway)
            {
                $result[] =
                [
                    "sysname"   => $gateway->name,
                    "name"      => $gateway->displayName,
                    "type"      => $gateway->getType(),
                ];
            }
        }
        else
        {
            foreach($this->reseller->settings->admin->gateways as $sysname)
            {
                $gateway = new PaymentGateway($sysname);

                $result[] = [
                    "sysname"   => $sysname,
                    "name"      => $gateway->name,
                    "type"      => $gateway->type
                ];
            }
        }

        return $result;
    }

    /**
     * Get enabled gateways in reseller store
     *
     * @return array|mixed
     */
    public function getEnabled()
    {
        //Return first available payment gateways if endclient invoice notifications are disabled
        if($this->reseller->settings->admin->disableEndClientInvoices)
        {
            $result[0] = ['sysname' => '']; 
            return $result;
        }

        //Get all gateways
        if(!$this->reseller->settings->admin->resellerInvoice)
        {
            return $this->reseller->settings->admin->gateways;
        }

        //Take only enabled gateways
        $result = [];
        $gateways = Helper::getCustomGateways($this->reseller->id);
        foreach($gateways as $key => $gateway)
        {
            if(!$gateway->enabled)
            {
                continue;
            }

            $result[] =
            [
                "sysname"        => $gateway->name,
                "name"           => $gateway->displayName,
                "normalised" => $gateway->getNormalisedName(),
                "type"           => $gateway->getType(),
            ];
        }

        return $result;
    }

    public function getEnabledSysNames()
    {
        $allGateways = $this->getEnabled();

        return array_map(function($value) {
            return $value['sysname'];
            }, $allGateways);
    }

    public function getEnabledNormalisedNames()
    {
        $allGateways = $this->getEnabled();

        return array_map(function($value) {
            return $value['normalised'];
        }, $allGateways);
    }
}