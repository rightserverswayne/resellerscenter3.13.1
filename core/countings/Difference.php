<?php
namespace MGModule\ResellersCenter\core\countings;
use MGModule\ResellersCenter\core\Counting;

use MGModule\ResellersCenter\models\whmcs\InvoiceItem;


/**
 * Description of Difference
 * This counting type is loaded as DEFAULT!!
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Difference extends Counting
{
    public $name = "Difference";
    
    public $description = "Selected option is difference between Reseller's and Admin's price";
    
    /**
     * Create configuration
     */
    public function __construct() 
    {
        /**
         * This type has no configuration
         */
    }
    
    /**
     * Get profit calculated from invoice item
     * 
     * @param InvoiceItem item
     * @param type $reseller
     */
    public function getProfit(InvoiceItem $item, \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller, $discount = 0)
    {
        $adminPrice = $this->getAdminPrice($item, $reseller);
        $resellerPrice = $this->getResellerPrice($item, $reseller) - $discount;
        
        $profit = $resellerPrice - $adminPrice;
        return $profit;
    }
}
