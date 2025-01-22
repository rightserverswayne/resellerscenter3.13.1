<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Services\Upgrades;

class ConfigurableOptionsUpgrade
{
    public static function getConfigOptionsAmount($productId, $billingCycle, $hostingId)
    {
        $configOptionsAmount = 0;

        if (!function_exists("getCartConfigOptions")) {
            require ROOTDIR . "/includes/configoptionsfunctions.php";
        }

        $configOptionsPricingArray = getCartConfigOptions($productId, "", $billingCycle, $hostingId);
        if ($configOptionsPricingArray) {
            foreach ($configOptionsPricingArray as $configOptionsPricings) {
                $configOptionsAmount += $configOptionsPricings["selectedrecurring"];
            }
        }

        return $configOptionsAmount;
    }
}