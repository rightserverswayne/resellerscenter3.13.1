<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Products\Products;
use MGModule\ResellersCenter\Core\Whmcs\WhmcsObject;

use Knp\Menu\MenuFactory;

/**
 * Description of Group
 *
 * @author Paweł Złamaniec
 */
class Group extends WhmcsObject
{
    public function getCartItemView()
    {
        $factory = new MenuFactory();
        $whmcsItem = new \WHMCS\View\Menu\Item(uniqid(), $factory);
        
        $whmcsItem->setName($this->name);
        $whmcsItem->setLabel($this->name);
        $whmcsItem->setUri("cart.php?gid={$this->id}");
        $whmcsItem->setDisplay(true);
        
        return $whmcsItem;
    }
    
    protected function getModelClass()
    {
        return "MGModule\ResellersCenter\models\whmcs\ProductGroup";
    }
}
