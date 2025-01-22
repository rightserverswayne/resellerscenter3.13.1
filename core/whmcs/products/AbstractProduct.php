<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Products;

use MGModule\ResellersCenter\Core\Resources\ResourceObject;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\Products\Pricing;

use MGModule\ResellersCenter\repository\Contents;

/**
 * Description of AbstractProduct
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
abstract class AbstractProduct extends ResourceObject
{
    /**
     * reseller object
     * 
     * @var \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller
     */
    public $reseller;
    
    /**
     * Content model
     * 
     * @var \MGModule\ResellersCenter\models\Content
     */
    public $content;

    /**
     * Content type
     * 
     * @var type
     */
    protected $contentType;

    /**
     * Initialize product object
     *
     * @param $idOrModel
     * @throws \ReflectionException
     */
    public function __construct($idOrModel, $reseller = null)
    {
        parent::__construct($idOrModel);

        //Set content and reseller if exists
        if($reseller->exists)
        {
            $this->reseller = $reseller;
            $this->findAndSetContent();
        }
    }

    /**
     * Get Pricing
     * 
     * @param Currency $currency
     * @return \MGModule\ResellersCenter\Core\Whmcs\Products\Pricing
     */
    public function getPricing(Currency $currency)
    {
        return new Pricing($this, $this->contentType, $currency);
    }

    /**
     * Get Decorator
     *
     * @param Currency $currency
     * @return mixed
     * @throws \ReflectionException
     */
    public function getDecorator(Currency $currency)
    {
        $namespace = (new \ReflectionClass($this))->getNamespaceName();
        $classname = $namespace . "\\Decorator";
        
        return new $classname($this, $currency);
    }

    /**
     * @param $billingcycle
     * @return string
     */
    public function getStandardizedBillingCycle($billingcycle)
    {
        if($billingcycle == "onetime")
        {
            $billingcycle = "monthly";
        }

        return $billingcycle;
    }

    /**
     * Return true if tax should be applied to this addon
     *
     * @return mixed
     */
    public function isTaxable()
    {
        return $this->__get("tax");
        return $this->model->tax;
    }

    /**
     * Set content model for loaded service
     */
    protected function findAndSetContent()
    {
        $contents = new Contents();
        $content = $contents->getContentByKeys($this->reseller->group_id, $this->id, $this->contentType);

        $this->content = $content;
    }
}