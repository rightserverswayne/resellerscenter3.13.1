<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Products\Products;
use MGModule\ResellersCenter\Core\Traits\HasModel;
use MGModule\ResellersCenter\Core\Traits\HasProperties;
use MGModule\ResellersCenter\Core\Whmcs\Products\AbstractProduct;
use MGModule\ResellersCenter\Core\Whmcs\Products\ServiceList;

use MGModule\ResellersCenter\repository\Contents;

/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Product extends AbstractProduct
{
    use HasModel, HasProperties
    {
        HasProperties::__get insteadof HasModel;
        HasModel::load as hasModelLoad;
    }

    /**
     * Content type
     *
     * @var string
     */
    protected $contentType = Contents::TYPE_PRODUCT;

    /**
     * Product configurable options object
     *
     * @var ConfigOptions
     */
    protected $configOptions;

    /**
     * Return object model class
     *
     * @return string
     */
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\models\whmcs\Product::class;
    }

    /**
     * Get possible updates
     *
     * @return ServiceList
     * @throws \ReflectionException
     */
    public function getPossibleUpgrades()
    {
        $upgrades = new ServiceList();
        foreach ($this->upgrades as $upgrade)
        {
            $new = new Product($upgrade->upgrade_product_id, $this->reseller);
            $upgrades->add($new);
        }

        return $upgrades;
    }

    /**
     * Check if product domain qualifies for free domain feature
     *
     * @param $period
     * @param $tld
     * @return bool
     */
    public function isDomainFree($tld)
    {
        $this->load();
        $tlds       = explode(",", $this->freedomaintlds);
        $periods    = explode(",", $this->freedomainpaymentterms);

        $result = false;
        if($this->freedomain && in_array($tld, $tlds) && in_array($this->getStandardizedBillingCycle($this->billingcycle), $periods))
        {
            $result = true;
        }

        return $result;
    }
}