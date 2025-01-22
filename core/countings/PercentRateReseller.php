<?php
namespace MGModule\ResellersCenter\core\countings;
use MGModule\ResellersCenter\core\Counting;

use MGModule\ResellersCenter\core\form\Form;
use MGModule\ResellersCenter\core\form\fields\Text;

use MGModule\ResellersCenter\models\ResellerSetting;
use MGModule\ResellersCenter\models\whmcs\InvoiceItem;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;

/**
 * Description of Difference
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class PercentRateReseller extends Counting
{
    public $name = "Reseller Percent With Rate";
    
    public $description = "Selected option is percentage of Reseller's price summed up with rate value.";
    
    /**
     * Create configuration
     */
    public function __construct() 
    {
        $percent = new Text("profit_percent", "Percent", "Provide percent value for selected counting type.");
        $percent->setValidators(array("required", "numeric", "aboveEq:0"));
        $percent->addStyle("width", 9);
        
        $rate = new Text("profit_rate", "Rate", "Provide value will amount of profit that he will get for each sale.");
        $rate->setValidators(array("required", "numeric", "aboveEq:0"));
        $rate->addStyle("width", 9);
        
        $this->configuration = new Form();
        $this->configuration->add($percent);
        $this->configuration->add($rate);
    }
    
    /**
     * Get profit calculated from invoice item
     * 
     * @param InvoiceItem item
     * @param type $reseller
     */
    public function getProfit(InvoiceItem $item, \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller, $discount = 0)
    {
        $percent = $this->configuration->get("profit_percent")->value;
        $rate = $this->configuration->get("profit_rate")->value;
                
        $resellerPrice = $this->getResellerPrice($item, $reseller) - $discount;
        
        // CUSTOM 3.6.1.2463

        $ConfigOptionsCommissions = ResellerSetting::where('reseller_id', $reseller->id)
                                                   ->where('setting', 'configoptions')
                                                   ->value('value');

        if( $ConfigOptionsCommissions == 'on' && $item->type == InvoiceItems::TYPE_SETUP
            || $item->type == InvoiceItems::TYPE_HOSTING
            || $item->type == InvoiceItems::TYPE_ABHOSTING
            || $item->type == InvoiceItems::TYPE_ABHOSTING_ITEM)
        {
            $resellerPrice = $this->getFixedResellerPrice($item, $reseller) - $discount;
        }
        else
        {
            $resellerPrice = $this->getResellerPrice($item, $reseller) - $discount;
        }
        $profit = $resellerPrice * $percent / 100 + $rate;
        
        return $profit;
    }
}
