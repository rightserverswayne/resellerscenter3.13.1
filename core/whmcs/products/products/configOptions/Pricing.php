<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Products\Products\ConfigOptions;

use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\repository\whmcs\Pricing as WhmcsPricingRepo;
use MGModule\ResellersCenter\models\whmcs\Pricing as WhmcsPricingModel;

/**
 * Description of Pricing
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Pricing 
{
    /**
     * Pricing model
     *
     * @var WhmcsPricingModel
     */
    protected $pricing;

    /**
     * Pricing constructor.
     *
     * @param int $suboptionid
     * @param Currency $currency
     * @param int $qty
     */
    public function __construct($suboptionid, Currency $currency)
    {
        $repo  = new WhmcsPricingRepo();
        $model = $repo->getPricingByRelIdAndType($suboptionid, WhmcsPricingRepo::TYPE_CONFIGOPTIONS, $currency->id);

        $this->pricing  = $model;
    }

    /**
     * Get price for provided billing cycle
     *
     * @param $billingcycle
     * @return mixed
     */
    public function getPrice($billingcycle)
    {
        return $this->pricing->{$billingcycle};
    }
}