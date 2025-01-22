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
class ConstantRate extends Counting
{
    public $name = "Constant Rate";
    
    public $description = "Selected option is constant rate value. Your reseller will always get the same profit despite of his sales size.";
    
    /**
     * Create configuration
     */
    public function __construct() 
    {
        $rate = new Text("profit_rate", "Rate", "Provide value will amount of profit that he will get for each sale.");
        $rate->setValidators(array("required", "numeric", "aboveEq:0"));
        $rate->addStyle("width", 9);
        
        $this->configuration = new Form();
        $this->configuration->add($rate);
    }
    
    /**
     * Get profit calculated from invoice item
     * 
     * @param type $item
     * @param type $reseller
     */
    public function getProfit(InvoiceItem $item, \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller, $discount = 0)
    {
        $profit = $this->configuration->get("profit_rate")->value - $discount;
        return $profit;
    }
}   
