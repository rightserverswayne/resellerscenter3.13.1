<?php
namespace MGModule\ResellersCenter\core\countings;
use MGModule\ResellersCenter\core\Counting;

use MGModule\ResellersCenter\core\form\Form;
use MGModule\ResellersCenter\core\form\fields\Text;

use MGModule\ResellersCenter\models\whmcs\InvoiceItem;
/**
 * Description of Difference
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class PercentDiff extends Counting
{
    public $name = "Percent From Difference";
    
    public $description = "Selected option is percentage of Reseller's price summed up with the difference between Reseller's and Admin's price.";
    
    /**
     * Create configuration
     */
    public function __construct() 
    {
        $percent = new Text("profit_percent", "Percent", "Provide percent value for selected counting type.");
        $percent->setValidators(array("required", "numeric", "aboveEq:0"));
        $percent->addStyle("width", 9);
        
        $this->configuration = new Form();
        $this->configuration->add($percent);
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
                
        $adminPrice = $this->getAdminPrice($item, $reseller);
        $resellerPrice = $this->getResellerPrice($item, $reseller) - $discount;
        
        $difference = $resellerPrice - $adminPrice;
        $profit = $difference * $percent / 100;
        
        return $profit;
    }
}
