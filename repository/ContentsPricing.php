<?php
namespace MGModule\ResellersCenter\repository;

use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\models\ContentPricing;
use MGModule\ResellersCenter\models\Content;

use MGModule\ResellersCenter\repository\whmcs\Addons;
use MGModule\ResellersCenter\repository\whmcs\Pricing;
use MGModule\ResellersCenter\repository\whmcs\Products;
use MGModule\ResellersCenter\repository\whmcs\Currencies;

use MGModule\ResellersCenter\repository\source\AbstractRepository;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of ContentsSettings
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ContentsPricing extends AbstractRepository
{
    const TYPE_ADMINPRCIE   = 'adminprice';
    
    const TYPE_HIGHESTPRICE = 'highestprice';
    
    const TYPE_LOWESTPRICE  = 'lowestprice';
    
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\ContentPricing';
    }
    
    /**
     * Save pricing for content.
     * price_type-> adminprice, highestprice or lowestprice
     * 
     * @since 3.0.0
     * @param int $contentid
     * @param array $pricing array(<currency_id> => array(<price_type> => array(<billing_cycle> => value)))
     */
    public function savePricing($contentid, $pricing)
    {
        foreach($pricing as $currency => $data)
        {
            foreach($data as $type => $prices)
            {
                foreach($prices as $billingcycle => $value)
                {
                    //empty pricing equals disabled!
                    $value = $value === '' ? -1 : $value;
                    
                    $cp = new ContentPricing();
                    $exists = $cp->findByKeys($contentid, $type, $currency, $billingcycle);
                    if($exists)
                    {
                        $cp->updateData($contentid, $currency, $type, $billingcycle, $value);
                    }
                    else
                    {
                        $cp->setData($contentid, $currency, $type, $billingcycle, $value);
                        $cp->save();
                    }
                }
            }
        }
    }
    
    public function deletePricing($contentid)
    {
        $pricing = $this->getModel();
        $pricing->where("relid", $contentid)->delete();
    }
        
    /**
     * Get content pricing.
     * Very similar to funcion getPricingByContentId but result from this funcion
     * is much more easy to use in PHP.
     * 
     * @param type $contentid
     * @param type $type
     * @param type $billingcycle
     * @return type
     */
    public function getPricing($contentid)
    {
        $query = DB::table("ResellersCenter_GroupsContentsPricing");
        $data = $query->where("relid", $contentid)->get();
        
        $result = array();
        foreach($data as $pricing)
        {
            $result[$pricing->currency][$pricing->billingcycle][$pricing->type] = $pricing->value;
        }
        
        return $result;
    }
    
    /**
     * Get available pricing for content.
     * This method gather data from WHMCS pricing setup
     * and based on that removes billingcycles and domain periods 
     * that are disabled in WHMCS system
     * 
     * @since 3.0.0
     * @param int $contentid
     * @return array (<currencyid> => array(<available_cycle>, ...));
     */
    public function getAvailablePricing($contentid)
    {
        $model = new Content();
        $content = $model->find($contentid);
        
        $repo = new Pricing();
        $pricing = $repo->getPricingByRelIdAndType($content->relid, $content->type);
        
        $result = array();
        foreach($pricing as $row)
        {
            foreach($row as $key => $value)
            {
                if($content->type == Pricing::TYPE_PRODUCT && !(in_array($key, Pricing::BILLING_CYCLES) || in_array($key, Pricing::SETUP_FEES))) {
                    continue;
                }

                //for setup fee check also recurring is available
                if ($content->type == Pricing::TYPE_PRODUCT && in_array($key, Pricing::SETUP_FEES)) {

                    $billingCycle = array_search($key, Pricing::SETUP_FEES);
                    $priceForBillingCycle = $row->$billingCycle;

                    if ($priceForBillingCycle < 0) {
                        continue;
                    }
                }
                                
                if(($content->type == Pricing::TYPE_DOMAINREGISTER && ($value < 0 || !in_array($key, Pricing::DOMAIN_PEROIDS))) || ($content->type == Pricing::TYPE_DOMAINRENEW && ($value < 0 || !in_array($key, Pricing::DOMAIN_PEROIDS))) || ($content->type == Pricing::TYPE_DOMAINTRANSFER && ($value < 0 || !in_array($key, Pricing::DOMAIN_PEROIDS))))
                {
                    continue;
                }

                if(($value < 0 && in_array($key, Pricing::BILLING_CYCLES)) || ($value <= 0 && in_array($key, Pricing::SETUP_FEES)) && !in_array($content->type, [Pricing::TYPE_DOMAINREGISTER, Pricing::TYPE_DOMAINRENEW, Pricing::TYPE_DOMAINTRANSFER]))
                {
                    continue;
                }

                //Since WHMCS 7.4 register pricing must be enabled for domains in order to set renewal and transfer
                if(Whmcs::isVersion("7.4") && ($content->type == Pricing::TYPE_DOMAINRENEW || $content->type == Pricing::TYPE_DOMAINTRANSFER))
                {
                    $register = $repo->getPricingByRelIdAndType($content->relid, Pricing::TYPE_DOMAINREGISTER, $row->currency);
                    if($register->{$key} < 0)
                    {
                        continue;
                    }
                }
                
                //Addon pricing behaviour is a bit different. WHMCS always save pricing for addons 
                //in msetupfee and monthly column regardless addon billing cycle...  (!)
                if($content->type == Pricing::TYPE_ADDON) 
                {
                    $addons = new Addons();
                    $addon = $addons->find($content->relid);
                    
                    if($addon->billingcycle == "recurring")
                    {
                        if(!(in_array($key, Pricing::BILLING_CYCLES) || in_array($key, Pricing::SETUP_FEES)))
                        {
                            continue;
                        }
                    }
                    else
                    {
                        $billingcycle = ($addon->billingcycle == "onetime") ? "monthly" : $addon->billingcycle;

                        if($row->monthly > 0)
                        {
                            $result[$row->currency][] = $billingcycle;
                        }

                        if($row->msetupfee > 0)
                        {
                            $setupfeeCycle = substr($billingcycle, 0, 1) . "setupfee";
                            $result[$row->currency][] = array_search($setupfeeCycle, Pricing::SETUP_FEES);
                            $result[$row->currency][] = $setupfeeCycle;
                        }

                        break;
                    }
                }
                
                $result[$row->currency][] = $key;
            }
        }

        return $result;
    }
    
    /**
     * Get pricing for product/addon/domain
     * 
     * @since 3.0.0
     * @param type $contentid
     * @return type
     */
    public function getPricingByContentId($contentid)
    {
        $query = DB::table("ResellersCenter_GroupsContentsPricing");
        $data = $query->where("relid", "=" , $contentid)->get();

        $pricing = array();
        foreach($data as $values)
        {
            $pricing[$values->currency][$values->type][$values->billingcycle] = $values->value;
        }
        
        $result = array();
        foreach($pricing as $currency => $data)
        {
            foreach($data as $type => $pricing)
            {
                $result[] = array(
                    "relid"     => $contentid,
                    "type"      => $type,
                    "currency"  => $currency,
                    "pricing"   => $pricing
                );
            }
        }
        
        return $result;
    }
    
    public function setDefaultPricing($contentid)
    {
        $contents = new Contents();
        $content = $contents->find($contentid);
        
        //Prepare Pricing
        $pricing = array();
        
        //Add pricing for free products (only records with 0)
        if(($content->type == Pricing::TYPE_PRODUCT && $content->product->paytype == Products::PAYTYPE_FREE) || ($content->type == Pricing::TYPE_ADDON && $content->addon->billingcycle == Addons::BILLINGCYCLE_FREE))
        {
            $repo = new Currencies();
            $currencies = $repo->all();
            
            foreach($currencies as $currency)
            {
                $pricing[$currency->id]["adminprice"]["free"]   =
                $pricing[$currency->id]["highestprice"]["free"] =
                $pricing[$currency->id]["lowestprice"]["free"]  = 0; 
            }
        }
        else
        {
            $available = $this->getAvailablePricing($contentid);

            foreach($available as $currency => $billingcycles)
            {
                foreach($billingcycles as $billingcycle)
                {
                    $whmcsBillingcycle = $billingcycle;
                    if($content->type == Pricing::TYPE_ADDON && $content->addon->billingcycle != "recurring"){
                        $whmcsBillingcycle = strpos($billingcycle, "setupfee") !== false ? "msetupfee" : "monthly";
                    }
                                
                    $repo = new Pricing();
                    $price = $repo->getPrice($content->type, $content->relid, $currency, $whmcsBillingcycle);
                    $pricing[$currency]["adminprice"][$billingcycle]    = 
                    $pricing[$currency]["highestprice"][$billingcycle]  =
                    $pricing[$currency]["lowestprice"][$billingcycle]   = $price; 
                }
            }
        }

        //Save Pricing
        $this->savePricing($content->id, $pricing);
    }
}