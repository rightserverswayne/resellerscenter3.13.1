<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Products;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\repository\whmcs\Currencies;

use MGModule\ResellersCenter\repository\whmcs\Products as ProductsRepo;
use MGModule\ResellersCenter\repository\whmcs\DomainPricing as DomainPricingRepo;
use MGModule\ResellersCenter\repository\whmcs\Addons as AddonsRepo;

/**
 * Description of Services
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Services 
{
    /**
     * Products available
     * 
     * @var orderView\products\ProductList 
     */
    public $products;
    
    /**
     * Available addons
     * 
     * @var orderView\products\AddonList
     */
    public $addons;
    
    /**
     * Available Domains
     * This is tricky - domains are handle by WHMCS different then addon and products
     * DomainList contains Domain objects that are parsed to correnct form in CartDecorator class
     * 
     * @var orderView\products\DomainList
     */
    public $domains;
    
    /**
     * Current currency
     * 
     * @var \MGModule\ResellersCenter\models\whmcs\Currency
     */
    public $currency;
    
    /**
     *
     * @var \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller
     */
    public $reseller;
    
    /**
     * Initialize whole object
     */
    public function __construct()
    {
        $this->products = new ServiceList();
        $this->domains = new ServiceList();
        $this->addons = new ServiceList();
    }
    
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
        return $this;
    }
    
    
    public function setReseller(\MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller)
    {
        $this->reseller = $reseller;
        return $this;
    }
    
    public function load()
    {
        $this->loadProducts();
        $this->loadDomains();
        $this->loadAddons();
        
        return $this;
    }
    
    public function loadForReseller()
    {
        if(!$this->reseller->exists)
        {
            throw new \Exception("Unable to load products for resellers. Reseller not provided.");
        }
        
        $repo = new Currencies();
        $currencies = $repo->all();
        foreach($currencies as $currency)
        {
            //Products
            
        }
    }
    
    public function getProductUpgrades($pid)
    {
        $product = $this->products->findOne("id", $pid);
        
        $upgrades = new ServiceList();
        foreach($product->upgrades as $upgrade)
        {
            $new = new Products\Product($upgrade->upgrade_product_id, $this->reseller);
            $upgrades->add($new);
        }
        
        return $upgrades;
    }
    
    private function loadProducts()
    {
        $repo = new ProductsRepo();
        $products = $repo->all();
        
        foreach($products as $product)
        {
            $new = new Products\Product($product, $this->reseller);
            $this->products->add($new);
        }
    }
        
    private function loadDomains()
    {
        $repo = new DomainPricingRepo();
        $domains = $repo->all();
        
        foreach($domains as $domain)
        {
            $new = new Domains\Domain($domain->id, $this->reseller, null, $domain);
            $this->domains->add($new);
        }
    }
    
    private function loadAddons()
    {
        $repo = new AddonsRepo();
        $addons = $repo->all();
        
        foreach($addons as $addon)
        {
            $new = new Addons\Addon($addon, $this->reseller);
            $this->addons->add($new);
        }
    }
}