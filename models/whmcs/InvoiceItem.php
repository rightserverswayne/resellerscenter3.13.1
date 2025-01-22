<?php

namespace MGModule\ResellersCenter\models\whmcs;

use \Illuminate\Database\Eloquent\model as EloquentModel;

use MGModule\ResellersCenter\Core\Whmcs\Services\Hosting\Hosting;
use MGModule\ResellersCenter\Core\Whmcs\Services\Domains\Domain;
use MGModule\ResellersCenter\Core\Whmcs\Services\Addons\Addon;
use MGModule\ResellersCenter\Core\Whmcs\Services\Hosting\Setup;
use MGModule\ResellersCenter\Core\Whmcs\Services\Upgrades\ProductUpgrade as ProductUpgradeService;

use MGModule\ResellersCenter\libs\CreditLine\Interfaces\InvoiceItemModelInterface;
use MGModule\ResellersCenter\repository\Contents;
use MGModule\ResellersCenter\repository\ResellersPricing;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;
use MGModule\ResellersCenter\repository\whmcs\DomainPricing as DomainPricingRepo;

use MGModule\ResellersCenter\core\helpers\DomainHelper;

use MGModule\ResellersCenter\Core\Whmcs\Products\Addons\Addon as ProductsAddon;
use MGModule\ResellersCenter\Core\Whmcs\Products\Domains\Domain as ProductsDomain;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\Product as ProductsProduct;
use MGModule\ResellersCenter\Core\Whmcs\Products\Upgrades\Upgrade as ProductsUpgrade;

/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class InvoiceItem extends EloquentModel implements InvoiceItemModelInterface
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblinvoiceitems';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('invoiceid', 'userid', 'type', 'relid', 'description', 'amount', 'taxed', 'duedate', 'paymentmethod', 'notes');

    /**
     * Indicates if the model should soft delete.
     *
     * @var bool
     */
    protected $softDelete = false;
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    
    
    public function client()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Client", "id", "userid");
    }
    
    public function invoice()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Invoice", "invoiceid");
    }
    
    public function hosting()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Hosting", "id", "relid");
    }

    public function aBHosting()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Hosting", "id", "relid");
    }
    
    public function hostingAddon()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\HostingAddon", "id", "relid");
    }
    
    public function domain()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Domain", "id", "relid");
    }
    
    public function upgrade()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Upgrade", "id", "relid");
    }
    
    public function profit()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\ResellerProfit", "invoiceitem_id");
    }
    
    public function getServiceAttribute()
    {
        $service = new \stdClass();
        $types = [InvoiceItems::TYPE_HOSTING, InvoiceItems::TYPE_ABHOSTING, InvoiceItems::TYPE_ABHOSTING_ITEM, InvoiceItems::TYPE_PROMO_HOSTING];
        $domainTypes = [InvoiceItems::TYPE_DOMAIN_REGISTER, InvoiceItems::TYPE_DOMAIN_RENEW, InvoiceItems::TYPE_DOMAIN_TRANSFER, InvoiceItems::TYPE_PROMO_DOMAIN];

        $pattern = "/".InvoiceItems::TYPE_COMPLETED_PREFIX . "(.+)" .InvoiceItems::TYPE_COMPLETED_SUFFIX . "/";
        $matches = [];
        $type = preg_match($pattern, $this->type, $matches) ? $matches[1] : $this->type;

        if (in_array($type, $types)) {
            $service = new Hosting($this->relid);
        } elseif ($type == InvoiceItems::TYPE_SETUP) {
            $service = new Setup($this->relid);
        } elseif ($type == InvoiceItems::TYPE_ADDON) {
            $service = new Addon($this->relid);
        } elseif (in_array($type, $domainTypes)) {
            $service = new Domain($this->relid);
        } elseif ($type == InvoiceItems::TYPE_UPGRADE) {
            $service = new ProductUpgradeService($this->relid);
        }

        return $service;
    }

    /**
     * Return type name from Content configuration
     * 
     * @return type
     */
    public function getContentTypeAttribute()
    {
        $types = [InvoiceItems::TYPE_HOSTING, InvoiceItems::TYPE_ABHOSTING, InvoiceItems::TYPE_ABHOSTING_ITEM, InvoiceItems::TYPE_SETUP];
        if(in_array($this->type, $types)) {
            return Contents::TYPE_PRODUCT;
        } elseif($this->type == InvoiceItems::TYPE_DOMAIN_RENEW) {
            return Contents::TYPE_DOMAIN_RENEW;
        } else {
            return strtolower($this->type);
        }
    }
    
    /**
     * Get relid to product/domain/addon from WHMCS configuration
     * 
     * @return type
     */
    public function getContentRelidAttribute()
    {
        $types = [InvoiceItems::TYPE_HOSTING, InvoiceItems::TYPE_ABHOSTING, InvoiceItems::TYPE_ABHOSTING_ITEM, InvoiceItems::TYPE_SETUP];
        if(in_array($this->type, $types))
        {
            $relid = $this->hosting->product->id;
        }
        elseif($this->type == InvoiceItems::TYPE_ADDON)
        {
            $relid = $this->hostingAddon->addon->id;
        }
        elseif($this->type == InvoiceItems::TYPE_DOMAIN_REGISTER ||$this->type == InvoiceItems::TYPE_DOMAIN_RENEW || $this->type == InvoiceItems::TYPE_DOMAIN_TRANSFER) 
        {
            $domain = $this->domain;
            $helper = new DomainHelper($domain->domain);

            $dp = new DomainPricingRepo();
            $domainPricing = $dp->getByTld($helper->getTLD());

            $relid = $domainPricing->id;
        }

        return $relid;
    }
    
    public function getProductObj($reseller)
    {
        $service = null;
        $relid = $this->getContentRelidAttribute();
        $types = [InvoiceItems::TYPE_HOSTING, InvoiceItems::TYPE_ABHOSTING, InvoiceItems::TYPE_ABHOSTING_ITEM, InvoiceItems::TYPE_SETUP];

        if( in_array($this->type, $types))
        {
            $service = new ProductsProduct($relid, $reseller);
        }
        elseif($this->type == InvoiceItems::TYPE_ADDON)
        {
            $service = new ProductsAddon($relid, $reseller);
        }
        elseif($this->type == InvoiceItems::TYPE_DOMAIN_REGISTER || $this->type == InvoiceItems::TYPE_DOMAIN_RENEW || $this->type == InvoiceItems::TYPE_DOMAIN_TRANSFER)
        {
            $type = $this->type == InvoiceItems::TYPE_DOMAIN_RENEW ? ResellersPricing::TYPE_DOMAINRENEW : $this->type;
            $service = new ProductsDomain($relid, $reseller, strtolower($type));
        }
        elseif($this->type == InvoiceItems::TYPE_UPGRADE)
        {
            $service = new ProductsUpgrade($this->relid, $reseller);
        }

        return $service;
    }
}
