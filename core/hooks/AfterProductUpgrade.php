<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\repository\whmcs\Upgrades;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\Product;
use MGModule\ResellersCenter\Core\Whmcs\Services\Upgrades\ConfigurableOptionsUpgrade;
/**
 * Description of AfterProductUpgrade
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class AfterProductUpgrade 
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     * 
     * @var array 
     */
    public $functions;
    
    /**
     * Assign anonymous function
     */
    public function __construct() 
    {
        $this->functions[10] = function($params) {
            return $this->setRecurringPriceForProduct($params);
        };
    }
    
    /**
     * Change hosting recurring amount after upgrade
     * 
     * @param type $params
     * @return type
     */
    public function setRecurringPriceForProduct($params)
    {
        $repo = new Upgrades();
        $upgrade = $repo->find($params["upgradeid"]);
        
        if(!$upgrade->hosting->resellerService)
        {
            return;
        }
        
        $reseller = new Reseller($upgrade->hosting->resellerService->reseller->id);
        $currency = new Currency($upgrade->hosting->client->currency);

        $newvalue = explode(",", $upgrade->newvalue);
        
        $newProduct   = new Product($newvalue[0], $reseller);
        $pricing      = $newProduct->getPricing($currency)->getBranded();

        $configurableOptionUpgradeCost = ConfigurableOptionsUpgrade::getConfigOptionsAmount($newvalue[0], $newvalue[1], $upgrade->hosting->id);

        $upgrade->hosting->amount = $pricing["pricing"][$newvalue[1]] + $configurableOptionUpgradeCost;
        $upgrade->hosting->save();
        
        return $params;
    }
}
