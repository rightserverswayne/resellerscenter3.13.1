<?php
namespace MGModule\ResellersCenter\Core\Cart\Totals;

use MGModule\ResellersCenter\Core\Cart\Totals\Products\Addon;
use MGModule\ResellersCenter\Core\Cart\Totals\Products\Domain;
use MGModule\ResellersCenter\Core\Cart\Totals\Products\Domains\Register;
use MGModule\ResellersCenter\Core\Cart\Totals\Products\Domains\Renew;
use MGModule\ResellersCenter\Core\Cart\Totals\Products\Domains\Transfer;
use MGModule\ResellersCenter\Core\Cart\Totals\Products\Product;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\repository\Contents;

/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Products
{
    /**
     * @var Reseller
     */
    protected $reseller;

    /**
     * Array of products
     *
     * @var mixed
     */
    protected $products = [];

    /**
     * Array of addons
     *
     * @var mixed
     */
    protected $addons  = [];

    /**
     * Array of domains
     *
     * @var mixed
     */
    protected $domains  = [];

    /**
     * Products constructor.
     * @param Reseller $reseller
     */
    public function __construct(Reseller $reseller)
    {
        $this->reseller = $reseller;
    }

    /**
     * Get all products from the object
     *
     * @return array
     */
    public function getAll()
    {
        $all = array_merge([], $this->addons, $this->domains, $this->products);
        return $all;
    }

    /**
     * Add Addon/Domain/Product to list
     *
     * @param $product
     * @throws \Exception
     */
    public function add($product)
    {
        switch(get_class($product))
        {
            case Addon::class:
                $this->addons[] = $product;
                break;
            case Domain::class:
                $this->domains[] = $product;
                break;
            case Product::class:
                $this->products[] = $product;
                break;
            default:
                throw new \Exception("Invalid product type provided");
        }
    }

    /**
     * Add item
     */
    public function addFromSource($type, $item)
    {
        switch($type)
        {
            case Contents::TYPE_PRODUCT:
                $product = Product::createFromSource($item, $this->reseller);
                $this->products[] = $product;

                //Add all product's addons
                if($product->addons)
                {
                    foreach ($product->addons as $addonid) {
                        $this->addons[] = Addon::createFromSource(["id" => $addonid, "billingcycle" => $product->billingcycle], $this->reseller);
                    }
                }
                break;
            case Contents::TYPE_ADDON:
                $this->addons[] = Addon::createFromSource($item, $this->reseller);
                break;
            case Contents::TYPE_DOMAIN_REGISTER:
            case Contents::TYPE_DOMAIN_TRANSFER:
                $this->domains[] = Domain::createFromSource($item, $this->reseller);
                break;
            case Contents::TYPE_DOMAIN_RENEW:
                $this->domains[] = Renew::createFromSource($item, $this->reseller);
            break;
        }
    }

    /**
     * Get subtotal composed from all products/addons/domains in cart
     *
     * @param Currency $currency
     * @return array
     */
    public function getRenewalsTotal(Currency $currency)
    {
        $result = [];
        foreach($this->domains as $domain)
        {
            if(!$domain instanceof Renew)
            {
                continue;
            }

            $result[$domain->id] = $domain->getSummarizeCartArray($currency);
        }

        return $result;
    }
}