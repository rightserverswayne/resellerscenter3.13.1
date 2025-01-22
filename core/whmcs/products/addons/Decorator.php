<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Products\addons;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;

/**
 * Description of AddonDecorator
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Decorator 
{
    private $addon;
    
    private $currency;
    
    private $pricing;
    
    public function __construct(Addon $addon, Currency $currency)
    {
        $this->addon = $addon;
        $this->currency = $currency;
        $this->pricing = $this->addon->getPricing($this->currency);
    }
    
    public function getGeneralData()
    {
        global $whmcs;

        $pricing = $this->pricing->getBrandedFull();
        $billingcycle = ($this->addon->billingcycle == "onetime") ? "monthly" : $this->addon->billingcycle;
        
        //Use lowest possible billing cycle when in WHMCS 7.2
        if(Whmcs::isVersion("7.2.0"))
        {
            $billingcycle = array_keys($pricing)[0];
        }
        
        $setupfeeCycle = substr($billingcycle, 0, 1) . "setupfee";
        
        $result = array(
            "id"            => $this->addon->id,
            "name"          => $this->addon->name,
            "description"   => $this->addon->description,
            "free"          => "",
            "recurringamount" => formatCurrency($pricing[$billingcycle], $this->currency->id),
            "billingcycle"  => $this->addon->billingcycle,
            "checkbox"      => "<input name='addons[{$this->addon->id}]' id='a{$this->addon->id}' type='checkbox'>",
        );
        
        $result["pricing"] = "{$result["recurringamount"]} {$this->addon->billingcycleFriendly}";
        if(isset($pricing[$setupfeeCycle]))
        {
            $result["setupfee"] = formatCurrency($pricing[$setupfeeCycle], $this->currency->id);
            $result["pricing"] .= " + {$result["setupfee"]} {$whmcs->get_lang("ordersetupfee")}";
        }
        
        return $result;
    }
    
    public function getProductIds(Client $client)
    {
        $result = array();
        $pids = explode(",", $this->addon->packages);
        $hostings = $client->hostings ?: [];
        foreach($hostings as $hosting)
        {
            if(in_array($hosting->product->id, $pids) && $hosting->domainstatus == "Active")
            {
                $product = array(
                    "id" => $hosting->id,
                    "product" => $hosting->product->name,
                    "domain" => $hosting->domain
                );
                $result[] = $product;
            }
        }
        
        return $result;
    }

    public function setTranslatedParams($translatedParams = [])
    {
        foreach ($translatedParams as $key=>$value) {
            if (!empty(trim($value))) {
                $this->addon->$key = $value;
            }
        }
    }
}
