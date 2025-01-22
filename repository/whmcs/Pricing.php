<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use MGModule\ResellersCenter\repository\source\AbstractRepository;

use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Pricing
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Pricing extends AbstractRepository
{
    const TYPE_ADDON = 'addon';
    
    const TYPE_PRODUCT = 'product';
    
    const TYPE_DOMAINREGISTER = 'domainregister';
    
    const TYPE_DOMAINTRANSFER = 'domaintransfer';
    
    const TYPE_DOMAINRENEW = 'domainrenew';
    
    const TYPE_CONFIGOPTIONS = 'configoptions';
    
    const TYPE_DOMAINADDONS = 'domainaddons';
    
    const BILLING_CYCLES =
    [
        "Free"          => "free",
        "Free Account"  => "freeaccount",
        "One Time"      => "onetime",
        "Monthly"       => 'monthly',
        "Quarterly"     => "quarterly",
        "Semi-Annually" => "semiannually",
        "Annually"      => "annually",
        "Biennially"    => "biennially",
        "Triennially"   => "triennially"
    ];
                             
    const SETUP_FEES =
    [
        "monthly"       => "msetupfee",
        "quarterly"     => "qsetupfee",
        "semiannually"  => "ssetupfee",
        "annually"      => "asetupfee",
        "biennially"    => "bsetupfee",
        "triennially"   => "tsetupfee"
    ];
    
    const DOMAIN_PEROIDS =
    [
        1 => "msetupfee",
        2 => "qsetupfee",
        3 => "ssetupfee",
        4 => "asetupfee",
        5 => "bsetupfee",
        6 => "monthly",
        7 => "quarterly",
        8 => "semiannually",
        9 => "annually",
        10 => "biennially"
    ];

    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Pricing';
    }
           
    public function getPricingByRelIdAndType($relid, $type, $currencyid = null)
    {
        $query = DB::table("tblpricing");
        $query->where("relid", $relid);
        $query->where("type", $type);
        
        if($currencyid != null) 
        {
            $query->where("currency", $currencyid);
            return $query->first();
        }
        
        return $query->get();
    }
    
    public function getPrice($type, $relid, $currency, $billingcycle)
    {
        $model = $this->getModel();
        $price = $model->where("type", $type)
                        ->where("relid", $relid)
                        ->where("currency", $currency)
                        ->first();
        
        return $price->{$billingcycle};
    }
}
