<?php
namespace MGModule\ResellersCenter\core\countings;
use MGModule\ResellersCenter\core\Counting;

use MGModule\ResellersCenter\core\form\Form;
use MGModule\ResellersCenter\core\form\fields\Text;

use MGModule\ResellersCenter\models\whmcs\InvoiceItem;
/**
 * Description of Admin Percent Plus Reseller Margin
 *
 * @author Damian Lipski <damian@modulesgarden.com>
 */
class AdminPercentPlusResellerMargin extends Counting
{
    public $name = "Admin Percent Plus Reseller Margin";
    
    public $description = "Selected option is percentage of Admin's price summed up with Reseller's margin.";
    
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
        $profit = $adminPrice * $percent / 100 + $difference;
        
        return $profit;
    }
}
