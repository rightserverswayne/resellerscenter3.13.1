<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Products\Addons;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Whmcs\Products\AbstractProduct;
use MGModule\ResellersCenter\repository\Contents;

/**
 * Description of Prodcut
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Addon extends AbstractProduct
{
    protected $contentType = Contents::TYPE_ADDON;

    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\models\whmcs\Addon::class;
    }

    public function getStandardizedBillingCycle($billingcycle)
    {
        if(Whmcs::isVersion("7.2.0"))
        {
            //Use hosting billing cycle
            if(empty($pricing[$billingcycle]))
            {
                $billingcycle = array_keys($pricing)[0];
            }
        }
        else
        {
            $billingcycle = parent::getStandardizedBillingCycle($this->model->billingcycle);
        }

        return $billingcycle;
    }

}