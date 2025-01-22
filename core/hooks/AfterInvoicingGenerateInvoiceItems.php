<?php
namespace MGModule\ResellersCenter\Core\Hooks;

use MGModule\ResellersCenter\core\helpers\CartHelper;
use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Resources\Invoices\Item;
use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice as ResellersCenterInvoice;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\ConfigOptions\Types\Quantity;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\ConfigOptions\Types\YesNo;
use MGModule\ResellersCenter\Core\Whmcs\Services\Addons\Addon;
use MGModule\ResellersCenter\Core\Whmcs\Services\Hosting\Hosting;
use MGModule\ResellersCenter\Core\Whmcs\Services\Domains\Domain;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings\EnableConsolidatedInvoices;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\repository\Invoices;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;
use MGModule\ResellersCenter\core\StaticFields;

use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\repository\whmcs\Pricing;
use MGModule\ResellersCenter\repository\whmcs\Taxes;

/**
 * Description of InvoiceCreation
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class AfterInvoicingGenerateInvoiceItems
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
        $this->functions[0] = function()
        {
            return $this->fixPromotionCode();
        };

        $this->functions[10] = function()
        {
            return $this->createResellersCenterInvoice();
        };

        $this->functions[20] = function()
        {
            return $this->prepareItemsForReseller();
        };

    }

    /**
     * Fix promocode on invoice
     */
    public function fixPromotionCode()
    {
        $repo = new InvoiceItems();
        $items = $repo->getItemsByInvoiceId(0);

        foreach($items as $item)
        {
            $reseller = Server::isRunByCron() ? Reseller::findByInvoiceItems([$item]) : Reseller::getCurrent();
            if($reseller->exists)
            {
               if(in_array($item->type, [InvoiceItems::TYPE_PROMO_HOSTING, InvoiceItems::TYPE_PROMO_DOMAIN]))
                {
                    $begining = substr($item->description, 0, strpos($item->description, ":") + 1);
                    $ending = substr($item->description, strpos($item->description, "_", strpos($item->description, "_") + 1) + 1);

                    $item->description = "{$begining} {$ending}";
                    $item->save();
                }
            }
        }
    }

    /**
     * Create Reseller's invoice for client
     */
    public function createResellersCenterInvoice()
    {
        global $whmcs;
        global $paymentmethod;

        $paymentmethod = (
            Session::get("rcChoosenGateway") ?:
            (Request::get('action') === 'addfunds' ? Request::get('paymentmethod') : null)
        ) ?: $paymentmethod;

        $repo = new InvoiceItems();       
        $uid = Reseller::isMakingOrderForClient() && Whmcs::isVersion('8.0') && Session::get('resellerid') ? Session::get('resellerid') : $_SESSION['uid']; 
        if(!empty($uid))
        {
            $raw = $repo->getItemsByInvoiceIdAndUserID(0, $uid);
        }
        else
        {
            $itemId = (int)StaticFields::getMaxInvoiceItemId(true);
            $raw    = $repo->getByInvoiceAndItemId(0, $itemId);
        }

        $allitems = [];
        foreach($raw as $item)
        {
            $allitems[$item->userid][] = $item;
        }

        $created = [];
        foreach($allitems as $userid => $items)
        {
            $reseller = Server::isRunByCron() ? Reseller::findByInvoiceItems($items) : Reseller::getCurrent();

            //Create Reseller invoice only if reseller does exist and has Reseller Invoice option enabled
            if(!empty($items) && $reseller->exists && $reseller->settings->admin->resellerInvoice)
            {
                if(Reseller::isMakingOrderForClient())
                {
                    $client = $reseller->clients->find($_SESSION['makeOrderFor'])->whmcsClient;
                }
                else
                {
                    $client = $reseller->clients->find($userid)->whmcsClient;
                }
                if(!$client->exists)
                {
                    continue;
                }

                if(Request::get('action') === 'domainaddons' && Request::get('confirm'))
                {
                    $paymentmethod = $reseller->gateways->getEnabled()[0]["sysname"];
                }

                $paymentmethod  = (
                    $paymentmethod && (
                        in_array($paymentmethod, $reseller->gateways->getEnabledNormalisedNames()) ||
                        in_array($paymentmethod, $reseller->gateways->getEnabledSysNames())
                    ))
                    ? $paymentmethod : $reseller->gateways->getEnabled()[0]["sysname"];

                $dueDateDays    = defined('SHOPPING_CART') ? $whmcs->get_config("OrderDaysGrace") : $whmcs->get_config("CreateInvoiceDaysBefore");
                $dueDateStrPart = $dueDateDays >= 0 ? '+ '.$dueDateDays : '- '.abs($dueDateDays);
                
                $invoice = new ResellersCenterInvoice();
                $invoice->create($reseller->id, $reseller->settings->getNextInvoiceNumber(), $client->id, date("Y-m-d"), date("Y-m-d", strtotime("{$dueDateStrPart} Days")), Invoices::STATUS_UNPAID, $paymentmethod);

                $isTaxInInvoice = false;

                foreach($items as $raw)
                {
                    if(!$raw->service->resellerService->exists && Server::isRunByCron())
                    {
                        continue;
                    }

                    $taxed = !$client->taxexempt ? $raw->taxed : 0;
                    $item = new Item();
                    $item->create($reseller->id, $invoice->id, $raw->userid, $raw->type, $raw->relid, $raw->description, $raw->amount, $taxed, $raw->duedate, $paymentmethod);

                    if($taxed)
                    {
                        $isTaxInInvoice = true;
                    }

                    //Remove original promotion items
                    if(in_array($raw->type, [InvoiceItems::TYPE_PROMO_HOSTING, InvoiceItems::TYPE_PROMO_DOMAIN, InvoiceItems::TYPE_GROUP_DISCOUNT]))
                    {
                        $raw->delete();
                    }
                }
                
                if($whmcs->get_config("TaxEnabled") && $isTaxInInvoice)
                {
                    $taxesRepo = new Taxes();
                    $invoice->taxrate   = $taxesRepo->getOnlyTaxesThatApply(1,$client->country,$client->state)->taxrate;
                    $invoice->taxrate2  = $taxesRepo->getOnlyTaxesThatApply(2,$client->country,$client->state)->taxrate;
                    $invoice->save();     
                }

                $invoice->updateInvoiceTotals();
                if(Request::get("applycredit"))
                {
                    Request::clear("applycredit");
                    \App::replace_input(Request::get());
                    $invoice->payments->applyCredits($invoice->total);
                }

                $created[] = $invoice->id;
                if(!$reseller->settings->admin->disableEndClientInvoices)
                {
                    $template = "Invoice Created";
                    if($invoice->payments->getGateway() && $invoice->payments->getGateway()->getType() == "CC")
                    {
                        $template = "Credit Card Invoice Created";
                    }

                    if (!SettingsManager::getSetting($invoice->userid, EnableConsolidatedInvoices::NAME) == 'on') {
                        $invoice->sendMessage($template);
                    }
                }
            }
        }

        Session::set("ResellerInvoices", $created);
    }

    /**
     * Set admin price on existing whmcs's invoice items
     */
    public function prepareItemsForReseller()
    {
        if (basename(Server::get("SCRIPT_NAME")) == "upgrade.php" &&
            Request::get("type") == "configoptions") {
            return;
        }

        //fix item amount
        $repo = new InvoiceItems();
        $items = $repo->getItemsByInvoiceId(0);

        foreach ($items as $item) {
            $currency = CartHelper::getCurrency();

            //Create Reseller invoice only if reseller does exist and has Reseller Invoice option enabled
            $reseller = Server::isRunByCron() ? Reseller::findByInvoiceItems([$item]) : Reseller::getCurrent();
            if ($reseller->exists && $reseller->settings->admin->resellerInvoice && $item->service->exists) {
                //We can't change user id here because WHMCS is using checking userid while creating invoice
                $billingcycle = $item->service->billingcycle == "onetime" ? "monthly" : $item->service->billingcycle;
                $billingcycle = $item->type == InvoiceItems::TYPE_SETUP ? Pricing::SETUP_FEES[$billingcycle] : $billingcycle;

                $product = $item->getProductObj($reseller);
                if (!$product) {
                    continue;
                }
                //Get base price
                $price = $product->getPricing($currency)->getAdminPrice($billingcycle);

                //Addon setup price is summarized in addon item on invoice
                if ($item->service instanceof Addon) {
                    $price += $product->getPricing($currency)->getAdminPrice(Pricing::SETUP_FEES[$billingcycle]);
                } elseif ($item->service instanceof Hosting) {
                    if($item->type != InvoiceItems::TYPE_SETUP && $item->type != InvoiceItems::TYPE_UPGRADE)
                    {
                        $product->configOptions->setServiceValues($item->service);
                        foreach ($product->configOptions as $config) {
                            $qty = $config->type instanceof Quantity || $config->type instanceof YesNo ? $config->value : 1;

                            $price += $config->type->getPricing($currency)->getPrice($billingcycle) * $qty;
                            $price += $config->type->getPricing($currency)->getPrice(Pricing::SETUP_FEES[$billingcycle]) * $qty;
                        }
                    }
                    $price = $price * ($item->hosting->qty ?: 1);
                } elseif ($item->service instanceof Domain && $item->amount == 0) {
                    $price = $item->amount;
                }

                $item->amount = Helper::calcCurrencyValue($price, $currency->id, $reseller->client->getCurrency()->id);
                $item->save();
            }
        }
    }

}
