<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Cart\Totals\Discount;
use MGModule\ResellersCenter\Core\Cart\Totals\Products\Domain;
use MGModule\ResellersCenter\Core\Cart\Totals\Products\Domains\Renew;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Whmcs\Promotions\Promotion;
use MGModule\ResellersCenter\repository\whmcs\Domains;
use MGModule\ResellersCenter\repository\whmcs\Orders;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;
use MGModule\ResellersCenter\core\StaticFields;

use MGModule\ResellersCenter\core\Session;
use MGModule\ResellersCenter\core\helpers\CartHelper;
use MGModule\ResellersCenter\core\helpers\ClientAreaHelper;
use MGModule\ResellersCenter\models\ResellerClient;
use MGModule\ResellersCenter\Core\Whmcs\Products\Domains\Domain as DomainService;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;

/**
 * Description of InvoiceCreation
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class PreInvoicingGenerateInvoiceItems 
{  
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     * 
     * @var array 
     */
    public $functions;
    
    /**
     * Container for hook params
     * 
     * @var type 
     */
    public static $params;
    
    /**
     * Assign anonymous function
     */
    public function __construct() 
    {
        $this->functions[0] = function() {
            $this->storeMaxInvoiceItemId();
            return $this->blockInvoiceSelectedItems();
        };
        
        $this->functions[10] = function() {
            return $this->changeOrderDataIfMakeOrderFor();
        };

        $this->functions[20] = function() {
            return $this->setDomainRenewPriceOnInvoice();
        };

        $this->functions[30] = function() {
            return $this->changeRecurringAmountOnDomainRegister();
        };
    }

    private function storeMaxInvoiceItemId()
    {
        $repo = new InvoiceItems();
        $maxId = $repo->getMaxItemId();
        StaticFields::storeMaxInvoiceItemId($maxId);
    }
    
    private function changeOrderDataIfMakeOrderFor()
    {
        if(!Reseller::isMakingOrderForClient() || Request::get('action') === 'addfunds' )
        {
            return;
        }

        $clientId = Session::get('makeOrderFor');

        /* Assigning the placed order to the choosen Client account */
        $orders = new Orders();
        $orderid = $orders->getModel()->max("id");
        $order = $orders->find($orderid);

        $order->userid = $clientId;
        $order->save();
    }
    
    public function blockInvoiceSelectedItems()
    {
        if(defined('ADMINAREA') && Request::get("action") == "massaction" && is_numeric(Request::get("userid")))
        {
            if(ResellerClient::where('client_id', Request::get("userid"))->first())
            {
                die('The Invoice Selected Items action is not supported by the Resellers Center module.');
            }
        }
    }
    
    public function setDomainRenewPriceOnInvoice()
    {
        //Check if client has a reseller
        $client = ClientAreaHelper::getLoggedClient();
        if(!$client->resellerClient->reseller->exists)
        {
            return;
        }
        
        $orders = new Orders();
        $orderid = $orders->getModel()->max("id");
        $order = $orders->find($orderid);

        $repo = new InvoiceItems();
        $items = $repo->getByInvoiceAndClient(0, $client->id);

        $reseller = Reseller::getCurrent();
        if(!$reseller->exists)
        {
            return;
        }
        
        $currency = CartHelper::getCurrency();
        
        foreach($items as $item)
        {
            if($item->type == InvoiceItems::TYPE_DOMAIN_RENEW)
            {
                $promocode = Session::get("cart.promo");

                $params = ["domainid" => $item->relid, "period" => Session::get("cart.renewals.{$item->relid}")];
                $domain = Renew::createFromSource($params, $reseller);
                $prices = $domain->getPrices($currency);


                if ($promocode) {
                    $promotion      = new Promotion(null, $promocode);
                    $this->discount = new Discount($promotion, new Client($item->invoice->client->id));
                }


                $domainService = new DomainService($domain->id, $reseller, null, $domain);
                if (!empty($this->discount)) {
                    $this->discount->getAndApply($domainService, $prices);
                }

                //Update order prices
                $order->amount += ($prices["today"] - $item->amount);
                $order->save();
                
                $item->amount = $prices["today"];
                $item->save();
                               
                //Update domain recurring amount
                $item->domain->recurringamount = array_values($prices["recurring"])[0];
                $item->domain->save();
            }
        }
    }

    public function changeRecurringAmountOnDomainRegister()
    {
        $client = ClientAreaHelper::getLoggedClient();
        if(!$client->resellerClient->reseller->exists || !ClientAreaHelper::isCartPage())
        {
            return;
        }

        $reseller = Reseller::getCurrent();
        
        if(!$reseller->exists)
        {
            return;
        }
        
        $currency = CartHelper::getCurrency();

        $domains = Session::get("cart.domains");
        if($domains)
        {
            foreach ($domains as $params) {
                $domain = Domain::createFromSource($params, $reseller);
                $prices = $domain->getPrices($currency);

                if (empty($prices["recurring"])) {
                    continue;
                }

                $repo = new Domains();
                $item = $repo->getByName($params["domain"])->last();

                $item->recurringamount = array_values($prices["recurring"])[0];
                $item->save();
            }
        }
    }

}
