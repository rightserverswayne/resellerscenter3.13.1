<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Products\Upgrades;

use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\Services\Upgrades\ConfigurableOptionsUpgrade;
use MGModule\ResellersCenter\repository\whmcs\Pricing as PricingRepo;

/**
 * Description of Pricing
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Pricing 
{
    /**
     * @var Upgrade
     */
    protected $upgrade;

    /**
     * @var Currency
     */
    protected $currency;

    protected $startNewPeriod = false;

    /**
     * Pricing constructor.
     *
     * @param Upgrade $upgrade
     * @param Currency $currency
     */
    public function __construct(Upgrade $upgrade, Currency $currency)
    {
        $this->upgrade = $upgrade;
        $this->currency = $currency;
    }

    /**
     * Admin price for provided billing cycle
     *
     * @return float|int
     * @throws \ReflectionException
     */
    public function getAdminPrice()
    {
        $oldProduct = $this->upgrade->getOldProduct();
        $oldProductPricing = $oldProduct->getPricing($this->currency)->getAdmin();

        $newProduct = $this->upgrade->getNewProduct();
        $newProductPricing = $newProduct->getPricing($this->currency)->getAdmin();

        $newBillingCycle = explode(",", $this->upgrade->newvalue)[1];
        $newBillingCycle = $newBillingCycle == 'onetime' ? 'monthly' : $newBillingCycle;
        $oldBillingCycle = Session::get("RC_OldBillingCycle") == 'onetime' ? 'monthly' : Session::get("RC_OldBillingCycle");

        $newPrice = $newProductPricing[$newBillingCycle]["adminprice"];

        if ($this->upgrade->hosting->billingcycle == "onetime") {
            $oldBillingCycle = $oldBillingCycle ?: $this->upgrade->hosting->billingcycle;
            $oldBillingCycle = $oldBillingCycle == 'onetime' ? 'monthly' : $oldBillingCycle;

            $oldPrice = $oldProductPricing[$oldBillingCycle]["adminprice"];
            $oldPrice += ConfigurableOptionsUpgrade::getConfigOptionsAmount($oldProduct->id, $oldBillingCycle, $this->upgrade->hosting->id);

            $newPrice += ConfigurableOptionsUpgrade::getConfigOptionsAmount($newProduct->id, $newBillingCycle, $this->upgrade->hosting->id);

            $credited = $oldPrice + $oldProductPricing[PricingRepo::SETUP_FEES[$oldBillingCycle]]["adminprice"];
            $debited  = $newPrice;
        } else {
            $oldPrice = $oldProductPricing[$oldBillingCycle]["adminprice"];
            $oldPrice += ConfigurableOptionsUpgrade::getConfigOptionsAmount($oldProduct->id, $oldBillingCycle, $this->upgrade->hosting->id);
            $credited = $oldPrice * $this->upgrade->getDaysUntilRenewal() / $this->upgrade->getOldTotalDays($oldBillingCycle);

            $totalNewDays = $this->upgrade->getNewTotalDays();
            $rate = $this->startNewPeriod || $totalNewDays == 0 ? 1 : $this->upgrade->getDaysUntilRenewal() / $totalNewDays;

            $newPrice += ConfigurableOptionsUpgrade::getConfigOptionsAmount($newProduct->id, $newBillingCycle, $this->upgrade->hosting->id);
            $debited = $newPrice * $rate;
        }

        return $debited - $credited;
    }

    public function getResellerPrice($billingcycle = null)
    {
        $newBillingcycle = explode(",", $this->upgrade->newvalue)[1];
        $newBillingcycle = $newBillingcycle == 'onetime' ? 'monthly' : $newBillingcycle;

        $oldBillingcycle = $billingcycle == 'onetime' ? 'monthly' : $billingcycle;

        $oldProduct = $this->upgrade->getOldProduct();
        $oldPrice = $oldProduct->getPricing($this->currency)->getBrandedPrice($oldBillingcycle);
        $oldPrice += ConfigurableOptionsUpgrade::getConfigOptionsAmount($oldProduct->id, $billingcycle, $this->upgrade->hosting->id);

        $newProduct = $this->upgrade->getNewProduct();
        $newPrice = $newProduct->getPricing($this->currency)->getBrandedPrice($newBillingcycle);
        $newPrice += ConfigurableOptionsUpgrade::getConfigOptionsAmount($newProduct->id, $billingcycle, $this->upgrade->hosting->id);

        if ($billingcycle == "onetime") {
            $credited = (float)$oldPrice + (float)$oldProduct->getPricing($this->currency)->getBrandedPrice(PricingRepo::SETUP_FEES[$oldBillingcycle]);
            $debited  = $newPrice;
        } else {
            $credited = $oldPrice * $this->upgrade->getDaysUntilRenewal() / $this->upgrade->getOldTotalDays($billingcycle);
            $totalNewDays = $this->upgrade->getNewTotalDays();
            $rate = ($this->startNewPeriod || $totalNewDays == 0) ? 1 : ($this->upgrade->getDaysUntilRenewal() / $totalNewDays);
            $debited = $newPrice * $rate;
        }

        return $debited - $credited;
    }

    public function setStartNewPeriodFlag()
    {
        $this->startNewPeriod = true;
    }
}
