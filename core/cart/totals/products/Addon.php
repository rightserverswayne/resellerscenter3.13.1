<?php

namespace MGModule\ResellersCenter\Core\Cart\Totals\Products;

use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\Products\Addons\Addon as WhmcsAddon;
use MGModule\ResellersCenter\Core\Whmcs\Services\Hosting\Hosting;
use MGModule\ResellersCenter\repository\whmcs\Pricing;

/**
 * Description of Addon
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Addon extends WhmcsAddon
{
    /**
     * @var int
     */
    protected $hostingid;

    /**
     * @var string
     */
    protected $billingcycle;

    /**
     * Create Addon object from params in cart
     *
     * @param $params
     * @param Reseller $reseller
     * @return Addon
     * @throws \ReflectionException
     */
    public static function createFromSource($params, Reseller $reseller)
    {
        /* Due the WHMCS 8 changes, addon's id and qty can be in other place */
        $addonId = !is_array($params['id']) ? $params['id'] : $params['id']['addonid'];
        $addonQty = !empty($params['qty']) ? $params['qty'] : $params['id']['qty'];

        $addon = new Addon($addonId, $reseller);
        $addon->hostingid    = $params["productid"];
        $addon->billingcycle = $params["billingcycle"];
        $addon->qty          = $addonQty;

        return $addon;
    }

    /**
     * Get Addon pricing
     *
     * @param Currency $currency
     * @return array
     */
    public function getPrices(Currency $currency)
    {
        $pricing        = $this->getPricing($currency)->getBrandedFull();
        $billingcycle   = $this->getBillingCycle();

        //If hosting billing cycle is not available get the shortest possible
        if(empty($pricing[$billingcycle]))
        {
            $billingcycle = array_keys($pricing)[0];
        }

        return
        [
            "today"     => $pricing[$billingcycle],
            "setupfee"  => $pricing[Pricing::SETUP_FEES[$billingcycle]],
            "recurring" =>
            [
                $billingcycle => $pricing[$billingcycle]
            ],
        ];
    }

    /**
     * Get addon billing cycle
     *
     * @return mixed
     */
    protected function getBillingCycle()
    {
        //Get service billing cycle if required
        if(!$this->billingcycle)
        {
            $hosting = new Hosting($this->hostingid);
            $this->billingcycle = $hosting->billingcycle;
        }

        //One time billing cycle pricing is save under `monthly`
        if((!$this->billingcycle && !Whmcs::isVersion("7.7.0")) || $this->billingcycle == "onetime")
        {
            $this->billingcycle = "monthly";
        }

        return $this->billingcycle;
    }
}