<?php

namespace MGModule\ResellersCenter\Core\Helpers;

use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\repository\whmcs\Addons;
use MGModule\ResellersCenter\repository\whmcs\Pricing;
use MGModule\ResellersCenter\repository\whmcs\Hostings;
use MGModule\ResellersCenter\repository\whmcs\Currencies;
use MGModule\ResellersCenter\core\Session;

/**
 * Description of CartHelper
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class CartHelper
{
    /**
     * Get addon information and return data in view cart format.
     * Note that WHMCS use productid key as hosting id...
     * 
     * @param mixed $addons
     * @return mixed $return
     */

    //Do wyrzucenia
    public static function getAddonsToCartViewObj($addon, $currency, $pricing)
    {
        global $whmcs;

        $addonsRepo  = new Addons();
        $hostingRepo = new Hostings();

        $hosting = $hostingRepo->find($addon["productid"]); //productid ↔ hostingid in this case
        $addon   = $addonsRepo->find($addon["id"]);

        //Pricing for onetime billing cycle is saved under monthly
        $billingcycle = ($addon->billingcycle == 'onetime') ? 'monthly' : $addon->billingcycle;
        if($billingcycle == "recurring")
        {
            //Use shortest possible if there is no pricing for hosting billingcycle
            $billingcycle = empty($pricing[$hosting->billingcycle]) ? array_keys($pricing)[0] : $hosting->billingcycle;
        }

        $pricetext = $currency["prefix"] . $pricing[$billingcycle] . $currency["suffix"];
        $setup     = '';

        if($pricing[Pricing::SETUP_FEES[$billingcycle]] !== null)
        {
            $pricetext .= ' + '. $currency["prefix"].$pricing[Pricing::SETUP_FEES[$billingcycle]].$currency["suffix"] . ' ' . $whmcs->get_lang("ordersetupfee");
            $setup = $currency["prefix"].$pricing[Pricing::SETUP_FEES[$billingcycle]].$currency["suffix"];
        }
        else //Get default setup fee
        {
            $defultSetupFee = $addon->getPricing($currency["id"])->msetupfee; //WHMCS always save addon prices in msetupfee/monthly
            $pricetext .= ' + ' . $currency["prefix"] . $defultSetupFee . $currency["suffix"] . ' ' . $whmcs->get_lang("ordersetupfee");
            $setup          = $currency["prefix"] . $defultSetupFee . $currency["suffix"];
        }

        $result = array(
            "name"                 => $addon->name,
            "productname"          => $hosting->product->name,
            "domainname"           => $hosting->domain,
            "pricingtext"          => $pricetext,
            "setup"                => $setup,
            "billingcycle"         => $addon->billingcycle,
            "billingcyclefriendly" => $addon->billingcycleFriendly
        );

        return $result;
    }

    /**
     * Get pricing for domain in cart search result.
     * Return array of pricing for all domain periods in cart format.
     * 
     * @param mixed $tld
     * @return mixed
     */
    public static function getDomainPricingForCart($register, $transfer, $renew, $currency)
    {
        $result = array();
        for ($i = 1; $i < 11; $i++)
        {
            $period = Pricing::DOMAIN_PEROIDS[$i];

            if (!empty($register[$period]))
            {
                $result[$i]["register"] = formatCurrency($register[$period], $currency["id"]);
            }

            if (!empty($transfer[$period]))
            {
                $result[$i]["transfer"] = formatCurrency($transfer[$period], $currency["id"]);
            }

            if (!empty($renew[$period]))
            {
                $result[$i]["renew"] = formatCurrency($renew[$period], $currency["id"]);
            }
        }

        return $result;
    }

    /**
     * Get Currency object
     *
     * @return Currency
     */
    public static function getCurrency()
    {
        $repo   = new Currencies();

        if(Reseller::isMakingOrderForClient())
        {
            $client = self::getCurrentClient();
            $model  = $client->exists ? $client->currencyObj : $repo->getDefault();
            return new Currency($model);
        }

        $switchedClientId = Session::get('RCSelectedAcc');
        if($switchedClientId)
        {
            $client = new Client($switchedClientId);
            $model  = $client->exists ? $client->currencyObj : $repo->getDefault();
            return new Currency($model);
        }

        $currencyid = Session::get("currency");
        if($currencyid)
        {
            $currency = new Currency($currencyid);
        }
        else
        {
            $client = ClientAreaHelper::getLoggedClient();
            $model  = $client->exists ? $client->currencyObj : $repo->getDefault();

            $currency = new Currency($model);
        }

        return $currency;
    }

    /**
     * Get client that should be on the order.
     * In case when Reseller is making order for client 
     * function will use client id from "makeOrderFor" var from _SESSION
     * 
     * @return type
     */
    public static function getCurrentClient()
    {
        //Get client that we are creating order for
        $clientid   = Session::get("makeOrderFor") ? : Session::get("uid");
        $client     = new Client($clientid);

        return $client;
    }

    /**
     * Get tax arrays
     *
     * @return array
     */
    public static function getTaxes($client = null)
    {
        if (!function_exists("getTaxRate")) {
            require_once Files::getWhmcsPath("includes","invoicefunctions.php");
        }

        global $whmcs;

        if (!$client->exists) {
            $client = self::getCurrentClient();
        }

        if ($client->exists) {
            $state      = $client->state;
            $country    = $client->country;
        } else {
            $_SESSION["cart"]["user"]["country"] = Session::get("cart.user.country") ?: $whmcs->get_config("DefaultCountry");
            $country    = Session::get("cart.user.state");
            $state      = Session::get("cart.user.state");
        }

        $tax1 = getTaxRate(1, $state, $country);
        $tax2 = getTaxRate(2, $state, $country);

        return ["tax1" => $tax1, "tax2" => $tax2];
    }
}
