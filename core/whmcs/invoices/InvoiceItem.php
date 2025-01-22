<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Invoices;

use MGModule\ResellersCenter\Core\Counting;
use MGModule\ResellersCenter\Core\Resources\Resellers\Promotions;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\WhmcsObject;
use MGModule\ResellersCenter\Core\Whmcs\Products\Upgrades\Upgrade;
use MGModule\ResellersCenter\Core\Resources\Promotions\Promotion as ResellerPromotion;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;


/**
 * Description of Item
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class InvoiceItem extends WhmcsObject
{
    /**
     * Get model class
     *
     * @return string
     */
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\models\whmcs\InvoiceItem::class;
    }

    public function create($params)
    {
        $class = $this->getModelClass();
        $this->model = new $class();

        $this->invoiceid     = $params['invoiceid'];
        $this->userid        = $params['userid'];
        $this->type          = $params['type'];
        $this->relid         = $params['relid'];
        $this->description   = $params['description'];
        $this->amount        = $params['amount'];
        $this->taxed         = $params['taxed'];
        $this->duedate       = $params['duedate'];
        $this->paymentmethod = $params['paymentmethod'];
        $this->save();
    }

    /**
     * Get related reseller object
     *
     * @return mixed
     */
    public function getReseller()
    {
        if($this->service->exists)
        {
            $reseller = $this->service->getReseller();
        }
        elseif(in_array($this->type, [InvoiceItems::TYPE_PROMO_HOSTING, InvoiceItems::TYPE_PROMO_DOMAIN]))
        {
            $code       = substr($this->description, strpos($this->description, ":")+2, strpos($this->description, " - ") - strpos($this->description, ":")-2);
            $promotion  = Promotion::getByCode($code);
            $reseller   = $promotion->getReseller();
        }

        return $reseller;
    }

    /**
     * Get profit for reseller
     *
     * @param \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller
     * @return float|int
     */
    public function getProfit(\MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller)
    {

        $discount = 0;
        //Special case
        if($this->type == InvoiceItems::TYPE_UPGRADE)
        {
            return $this->getUpgradeProfit($reseller);
        }
        
        //Ignore Discounts
        $discountTypes = [InvoiceItems::TYPE_GROUP_DISCOUNT, InvoiceItems::TYPE_PROMO_DOMAIN, InvoiceItems::TYPE_PROMO_HOSTING];
        if(in_array($this->type, $discountTypes))
        {
            return 0;
        }
        
        //Look for promotions on current item
        $promotion = $this->getRelatedPromotion($reseller);
        if($promotion->exists)
        {
            $billingcycle = $this->service->billingcycle != "onetime" ? $this->service->billingcycle : "monthly";
            if($this->type == InvoiceItems::TYPE_SETUP)
            {
                $billingcycle = substr($billingcycle, 0, 1) . "setupfee";
            }

            $currency = new Currency($this->client->currencyObj);
            $discount = (int)$promotion->getDiscountAmount($this->model->getProductObj($reseller), $billingcycle, $currency);
        }

        //Check price difference
        if($this->hasResellerPricing($reseller))
        {
            $counting = $this->getCountingObject($reseller->group_id);

            $diff = ($counting->getResellerPrice($this->model, $reseller) - $discount) - $counting->getAdminPrice($this->model, $reseller);
            if($diff < 0)
            {
                //Reseller is selling in lower price then admin price and he have to pay extra
                return $diff;
            }

            //Get profit for reseller
            $profit = $counting->getProfit($this->model, $reseller, $discount);

            return $profit;
        }

        return 0;
    }

    /**
     * Get promotion for this item
     *
     * @param $reseller
     * @return \MGModule\ResellersCenter\Core\Whmcs\Promotions\Promotion\|null
     */
    public function getRelatedPromotion($reseller)
    {
        if(!in_array($this->type, [InvoiceItems::TYPE_HOSTING, InvoiceItems::TYPE_SETUP, InvoiceItems::TYPE_DOMAIN_REGISTER, InvoiceItems::TYPE_DOMAIN_TRANSFER, InvoiceItems::TYPE_DOMAIN_RENEW]))
        {
            return null;
        }

        $type = ($this->type == InvoiceItems::TYPE_HOSTING || $this->type == InvoiceItems::TYPE_SETUP) ? InvoiceItems::TYPE_PROMO_HOSTING : InvoiceItems::TYPE_PROMO_DOMAIN;
        $repo = new InvoiceItems();
        $item = $repo->getByInvoiceAndRelidAndType($this->invoiceid, $this->relid, $type);

        if($item->exists)
        {
            //Omg...
            $code = substr($item->description, strpos($item->description, ":")+2, strpos($item->description, " - ") - strpos($item->description, ":")-2);
            $promotion = new ResellerPromotion(null, $code, $reseller);
        }
        
        return $promotion;
    }

    /**
     * Check if reseller has pricing for the item
     *
     * @param Reseller $reseller
     * @return bool
     */
    private function hasResellerPricing(Reseller $reseller)
    {
        $result = false;
        $service = $this->model->getProductObj($reseller);
        if($service !== null)
        {
            $currency = new Currency($this->client->currencyObj);
            $pricing  = $service->getPricing($currency);

            //Get price for billing cycle
            $billingcycle = $this->service->billingcycle == "onetime" ? "monthly" : $this->service->billingcycle;
            $price = $pricing->getBrandedPrice($billingcycle);

            if($price !== null)
            {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Get upgrade profit
     *
     * @param $reseller
     * @return float|int
     */
    private function getUpgradeProfit($reseller)
    {
        $currency   = new Currency($this->client->currencyObj);
        $upgrade    = new Upgrade($this->relid, $reseller);
        $adminPrice = $upgrade->getPricing($currency)->getAdminPrice();
                
        //Get different in price between Admin price and Reseller Price
        $profit = $this->amount - $adminPrice;
        return $profit;
    }

    /**
     * Get counting object
     *
     * @param $groupid
     * @return \MGModule\ResellersCenter\core\counting\
     */
    private function getCountingObject($groupid)
    {
        $contents = new \MGModule\ResellersCenter\Repository\Contents();
        $content = $contents->getContentByKeys($groupid, $this->contentRelid, $this->contentType);
        
        $config = $content->getConfig();
        $counting = Counting::factory($config["type"], $config["settings"]);
        
        return $counting;
    }
}
