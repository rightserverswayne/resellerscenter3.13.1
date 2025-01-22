<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Products\products;

use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use \WHMCS\Database\Capsule as DB;
/**
 * Description of Decorator
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Decorator 
{
    private $product;
    
    private $currency;
    
    private $pricing;
    
    public function __construct(Product $product, Currency $currency)
    {
        $this->product = $product;
        $this->currency = $currency;
        $this->pricing = $this->product->getPricing($this->currency);
    }
    
    public function getGeneralData()
    {
        $features = $this->getFeatures($this->product->description);

        if( $GLOBALS['CONFIG']['EnableTranslations'] )
        {
            $selectedLanguage      = $_SESSION['Language'];
            $translatedName        = DB::table('tbldynamic_translations')
                                       ->select('translation')
                                       ->where('related_id', '=', $this->product->id)
                                       ->where('related_type', '=', 'product.{id}.name')
                                       ->where('language', '=', $selectedLanguage)
                                       ->value('translation');
            $productName           = $translatedName ? : $this->product->name;

            $translatedDescription = DB::table('tbldynamic_translations')
                                       ->select('translation')
                                       ->where('related_id', '=', $this->product->id)
                                       ->where('related_type', '=', 'product.{id}.description')
                                       ->where('language', '=', $selectedLanguage)
                                       ->value('translation');
            $productDescription = $translatedDescription ? : $this->product->description;
        }
        else
        {
            $productName = $this->product->name;
            $productDescription = $this->product->description;
        }
        return array(
            "pid"           => $this->product->id,
            "bid"           => 0,
            "gid"           => $this->product->group->id,
            "groupname"     => $this->product->group->name,
            "group_name"     => $this->product->group->name,
            "type"          => $this->product->type,
            "name"          => $productName,
            "description"   => nl2br($productDescription),
            "features"      => $features,
            "featuresdesc"  => empty($features) ? nl2br($productDescription) : "",
            "paytype"       => $this->product->paytype,
            "freedomain"    => $this->product->freedomain,
            "freedomainpaymentterms" => $this->product->freedomainpaymentterms,
            "qty"           => $this->product->stockcontrol ? $this->product->qty : null,
            "isFeatured"    => $this->product->is_featured,
            "order"         => $this->product->order,
        );
    }
    
    public function getPricing()
    {
        $result = array(
            "type" => $this->product->paytype,
            "hasconfigoptions" => "",
            "cycles" => $this->getCyclesPricing(),
            "rawpricing" => $this->getRawPricing(),
            "minprice" => $this->getMiniPricing()
        );

        $result[$this->product->paytype] = $result['cycles'][$this->product->paytype];
        if( $this->product->paytype === 'onetime' )
        {
            $result['cycles']            = ['onetime' => $result['cycles'][$this->product->paytype]];
            $result['minprice']['cycle'] = 'onetime';
        }
        $final = array_merge($result, $this->getCyclesPricing());

        
        return $final;
    }
    
    public function getCyclesPricing()
    {
        $result = array();
        $pricing = $this->pricing->getBrandedFull();

        foreach($pricing as $billingcycle => $price)
        {
            if(strpos($billingcycle, "setupfee") !== false) {
                continue;
            }
                        
            $setupfeeCycle = substr($billingcycle, 0, 1) . "setupfee";
            $recurring = formatCurrency($price, $this->currency->id);
            $setupfee  = isset($pricing[$setupfeeCycle]) ? "+ " . formatCurrency($pricing[$setupfeeCycle], $this->currency->id) : "";
            
            $friendly = array_search($billingcycle, \MGModule\ResellersCenter\repository\whmcs\Pricing::BILLING_CYCLES);
            $result[$billingcycle] = "{$recurring} {$friendly} {$setupfee}";
            
            //Special case for prenium_comparison, pure_comparison, supreme_comparison and universal_slider
            if($this->product->paytype == "onetime")
            {
                global $whmcs;
                $result["onetime"] = "{$recurring} {$setupfee} {$whmcs->get_lang("ordersetupfee")}";
                break;
            }
        }

        return $result;
    }
    
    public function getRawPricing()
    {
        return $this->pricing->getBrandedFull();
    }

    public function getMiniPricing()
    {
        $pricing = $this->pricing->getBrandedFull();

        $shortest = null;
        foreach(\MGModule\ResellersCenter\repository\whmcs\Pricing::BILLING_CYCLES as $billingcycle)
        {
            if(isset($pricing[$billingcycle]))
            {
                $shortest = $billingcycle;
                $setupfee = substr($billingcycle, 0, 1) . "setupfee";
                break;
            }
        }

        $mini =
        [
            "price"     => formatCurrency($pricing[$shortest], $this->currency->id),
            "cycle"     => $shortest,
            "setupFee"  => isset($pricing[$setupfee]) ? formatCurrency($pricing[$setupfee], $this->currency->id) : null,
            "simple"    => $this->currency->prefix . $pricing[$shortest]
        ];

        $mini["cycleText"]              = "<span>{$mini["simple"]}</span>/" . substr($shortest, 0, 2);
        $mini["cycleTextWithCurrency"]  = "<span>{$mini["price"]}</span>/" . substr($shortest, 0, 2);

        return $mini;
    }

    public function setTranslatedParams($translatedParams = [])
    {
        if (empty($translatedParams)) {
            return;
        }

        foreach ($translatedParams as $key=>$value) {
            if (!empty(trim($value))) {
                $this->product->$key = $value;
            }
        }
    }
    
    private function getFeatures($description)
    {
        $result = [];
        $matches = [];
        preg_match_all("(.*: .*)", $description, $matches);
        foreach($matches[0] as $match)
        {
            $pos = strpos($match, ":");
            $key = substr($match, 0, $pos);
            $value = substr($match, $pos+1);
            
            $result[$key] = trim("$value");
        }

        return $result;
    }
}
