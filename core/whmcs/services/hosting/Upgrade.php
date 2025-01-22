<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Services\Hosting;

use DateTime;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\Product;
use MGModule\ResellersCenter\Core\Whmcs\Services\Upgrades\ConfigurableOptionsUpgrade;
use MGModule\ResellersCenter\repository\whmcs\Pricing as PricingRepo;

/**
 * Description of Upgrade.php
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Upgrade
{
    /**
     * @var Hosting
     */
    protected $hosting;
    protected $startNewPeriod = false;

    /**
     * @var Product
     */
    protected $newProduct;

    /**
     * @var string
     */
    protected $newBillingCycle;

    protected $reseller;

    /**
     * Upgrade constructor.
     *
     * @param Hosting $hosting
     * @param Product $product
     * @param $billingycle
     */
    public function __construct(Hosting $hosting, Product $product, $billingycle)
    {
        $this->hosting          = $hosting;
        $this->newProduct       = $product;
        $this->newBillingCycle  = $billingycle;
        $this->reseller         = $product->reseller;
    }

    public function setStartNewPeriodFlag()
    {
        $this->startNewPeriod = true;
    }

    /**
     * Get Upgrade price
     *
     * @return float|int
     */
    public function getPrice()
    {
        return $this->getDebitedPrice($this->newProduct, $this->newBillingCycle) - $this->getCreditedPrice();
    }

    /**
     * @return float|int
     */
    public function getAdminPrice()
    {
        return $this->getDebitedPrice($this->newProduct, $this->newBillingCycle, true) - $this->getCreditedPrice();
    }

    /**
     * @param $billingycle
     * @return float|int
     */
    protected function getCreditedPrice()
    {
        $model      = $this->hosting->client->currencyObj;
        $currency   = new Currency($model);
        $cycle      = $this->hosting->billingcycle == "onetime" ? "monthly" : $this->hosting->billingcycle;
        $product = new Product($this->hosting->product, $this->reseller );

        $newProductPrice = $product->getPricing($currency)->getBrandedPrice($cycle);
        $newProductPrice += ConfigurableOptionsUpgrade::getConfigOptionsAmount($this->hosting->product->id, $this->hosting->billingcycle, $this->hosting->id);

        if ($this->hosting->billingcycle == "onetime") {
            $newProductPrice += (float) $product->getPricing($currency)->getBrandedPrice(PricingRepo::SETUP_FEES[$cycle]);
        } else {
            $newProductPrice *= $this->getDaysUntilRenewal() / $this->getCurrentTotalDays();
        }

        return $newProductPrice;
    }

    /**
     * @param $product
     * @param $billingcycle
     * @return float|int
     */
    protected function getDebitedPrice(Product $product, $billingcycle, $admin = false)
    {
        $model      = $this->hosting->client->currencyObj;
        $currency   = new Currency($model);
        $cycle      = $billingcycle == "onetime" ? "monthly" : $billingcycle;

        //Check what type of price do we want to get
        $price = $admin ? $product->getPricing($currency)->getAdminPrice($cycle) : $product->getPricing($currency)->getBrandedPrice($cycle);

        $price += ConfigurableOptionsUpgrade::getConfigOptionsAmount($product->id, $billingcycle, $this->hosting->id);

        if($billingcycle != "onetime" && !$this->startNewPeriod)
        {
            $price *= $this->getDaysUntilRenewal() / $this->getNewTotalDays($cycle);
        }

        return $price;
    }

    /**
     * Get Days remaining for the hosting renewal
     *
     * @return float
     */
    public function getDaysUntilRenewal()
    {
        $datediff = strtotime($this->hosting->nextduedate) - time();
        return ceil($datediff / (3600 * 24));
    }

    /**
     * Get Current total days
     *
     * @return float
     */
    public function getCurrentTotalDays()
    {
        $prevduedate = $this->getPrevDueDate();
        $totaldays = round((strtotime($this->hosting->nextduedate) - strtotime($prevduedate)) / 86400);

        return $totaldays;
    }

    /**
     * @return float
     */
    public function getNewTotalDays($billingcycle)
    {
        $year   = substr($this->hosting->nextduedate, 0, 4);
        $month  = substr($this->hosting->nextduedate, 5, 2);
        $day    = substr($this->hosting->nextduedate, 8, 2);

        $newCycleMonths = getBillingCycleMonths($billingcycle);
        $prevduedate = date("Y-m-d", mktime(0, 0, 0, $month - $newCycleMonths, $day, $year));
        $newTotalDays = round((strtotime($this->hosting->nextduedate) - strtotime($prevduedate)) / 86400);

        return $newTotalDays;
    }

    public function getDescription()
    {
        $product = $this->hosting->getRelatedProduct();

        $lang = Whmcs::lang("upgradedowngradepackage");

        $dateTime = new DateTime("NOW");

        $prevDueDate = $this->startNewPeriod ? $dateTime->format("Y-m-d") : $this->getPrevDueDate();
        $nextDueDate = $this->getNextDueDate();

        $result = "{$lang}: {$product->name} - {$this->hosting->domain}  {$product->name} => {$this->newProduct->name} ({$prevDueDate} - {$nextDueDate})";

        return $result;
    }

    public function getNextDueDate()
    {
        try {
            if (!$this->startNewPeriod) {
                throw new \Exception();
            }
            $dateTime = new DateTime("NOW");
            $oldCycleMonths = getBillingCycleMonths($this->hosting->billingcycle);
            $dateInterval = new \DateInterval('P'.$oldCycleMonths.'M');
            $dateTime->add($dateInterval);
        } catch (\Exception $e) {
            return $this->hosting->nextduedate;
        }

        return $dateTime->format("Y-m-d");
    }

    protected function getPrevDueDate()
    {
        $year   = substr($this->hosting->nextduedate, 0, 4);
        $month  = substr($this->hosting->nextduedate, 5, 2);
        $day    = substr($this->hosting->nextduedate, 8, 2);

        $oldCycleMonths = getBillingCycleMonths($this->hosting->billingcycle);
        $result = date("Y-m-d", mktime(0, 0, 0, $month - $oldCycleMonths, $day, $year));

        return $result;
    }
}