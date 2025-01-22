<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings\EnableConsolidatedInvoices;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\repository\Invoices as RCInvoicesRepo;
use MGModule\ResellersCenter\repository\InvoiceItems;
use MGModule\ResellersCenter\repository\BrandedInvoices;
use MGModule\ResellersCenter\repository\Resellers;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\repository\whmcs\Invoices;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems as WhmcsInvoiceItems;

use MGModule\ResellersCenter\Core\Whmcs\Products\Upgrades\Upgrade;
use MGModule\ResellersCenter\Core\Whmcs\Invoices\Invoice as WhmcsInvoice;
use MGModule\ResellersCenter\Core\Resources\Invoices\Item;
use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice as ResellersCenterInvoice;

use MGModule\ResellersCenter\core\Server;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\core\Session;
use MGModule\ResellersCenter\core\Redirect;
use MGModule\ResellersCenter\Core\Configuration;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
/**
 * Description of InvoiceCreation
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class InvoiceCreation
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     * 
     * @var array 
     */
    public $functions;
    
    public static $params;
    
    /**
     * Assign anonymous function
     */
    public function __construct() 
    {
        $this->functions[70] = function($params)
        {
            self::$params = $this->updateItemsIfMakeOrderFor($params);
        };

        $this->functions[80] = function($params)
        {
            self::$params = $this->createBrandedInvoiceNumber($params);
        };

        if(basename(Server::get("SCRIPT_NAME")) == "upgrade.php")
        {
            $this->functions[60] = function($params)
            {
                self::$params = $this->fixUpgradeInvoiceForReseller($params);
            };

            return;
        }

        $this->functions[PHP_INT_MAX] = function()
        {
            return $this->updateNewInvoices(self::$params);
        };
    }

    private function updateItemsIfMakeOrderFor($params)
    {
        if(!Reseller::isMakingOrderForClient())
        {
            return $params;
        }

        $clientId = Session::get('makeOrderFor');

        $repo = new WhmcsInvoiceItems();
        $items = $repo->getItemsByInvoiceId($params["invoiceid"]);

        foreach($items as $item)
        {
            $item->userid = $clientId;
            $item->save();
        }

        if( !Reseller::getCurrent()->settings->admin->resellerInvoice )
        {
            $whmcsInvoice         = new WhmcsInvoice($params['invoiceid']);
            $whmcsInvoice->userid = $clientId;
            $whmcsInvoice->save();

            global $CONFIG;
            if( !empty($CONFIG['StoreClientDataSnapshotOnInvoiceCreation']) )
            {
                $whmcsInvoice->refreshSnaphot();
            }
        }

        if(Whmcs::isVersion('8.0.0'))
        {
            $client =  \WHMCS\User\Client::where("id",$clientId)->first();
            $params['user'] = $client->owner()->id;
        }
        
        return $params;
    }

    public function createBrandedInvoiceNumber($params)
    {
        if ($params['invoiceid']) {
            $invoice = new \WHMCS\Invoice($params['invoiceid']);
            $data = $invoice ? $invoice->getData() : [];
            $params['user'] = $data['userid'] ?: $params['user'];
        }

        //Get related reseller
        $reseller = ResellerHelper::getCurrent();

        if (!$reseller->exists) {
            $repo = new ResellersClients();
            $reseller = $repo->getResellerObjectByHisClientId($params['user']);
        }

        if (Server::isRunByCron()) {
            $invoice = new WhmcsInvoice($params["invoiceid"]);
            $reseller = $invoice->getReseller();
        }

        //Run only for WHMCS invoice for reseller <-> end client invoices
        if (!$reseller->exists || $reseller->settings->admin->resellerInvoice) {
            return $params;
        }

        $repo = new Invoices();
        $invoiceObj = $repo->find($params["invoiceid"]);

        if ($reseller->settings->admin->invoiceBranding && !empty($invoiceObj)) {
            $invoiceNum = $reseller->settings->getNextInvoiceNumber();
            if (empty($invoiceNum)) {
                return $params;
            }
            $brandedInvoice = new BrandedInvoices();
            $brandedInvoice->createNew($reseller->id, $invoiceObj->id, $invoiceNum);
        }

        return $params;
    }

    /**
     * Fix upgrade invoice for a reseller.
     * Items on the invoice generated for a reseller has prices based on reseller pricing configuration
     *
     * @param $params
     * @return mixed
     */
    public function fixUpgradeInvoiceForReseller($params)
    {
        $reseller = ResellerHelper::getCurrent();
        if (!$reseller->exists || !$reseller->settings->admin->resellerInvoice) {
            return $params;
        }

        global $CONFIG;

        $CONFIG['NoAutoApplyCredit'] = SettingsManager::getSettingFromReseller($reseller, EnableConsolidatedInvoices::NAME) == 'on' ?:
            $CONFIG['NoAutoApplyCredit'];

        $invoice = new WhmcsInvoice($params["invoiceid"]);
        $upgrade = new Upgrade($invoice->items[0]->relid, $reseller);

        $currency = new Currency($reseller->client->currencyObj);
        $price = $upgrade->getPricing($currency)->getAdminPrice();

        if ($price < 0) {
            $invoice->delete();
        } else {
            $invoice->userid = $reseller->client->id;
            $invoice->save();
            if (!empty($CONFIG['StoreClientDataSnapshotOnInvoiceCreation'])) {
                $invoice->refreshSnaphot();
            }

            updateInvoiceTotal($invoice->id);

            //Set relinvoice
            $invoiceid = Session::get("ResellerInvoices")[0];
            if ($invoiceid) {
                //Update resellers center invoice
                $invoice = new ResellersCenterInvoice($invoiceid);
                $invoice->relinvoice_id = $params["invoiceid"];
                $invoice->save();

                \WHMCS\Invoice::saveClientSnapshotData($invoiceid);
            }
        }

        return $params;
    }

    /**
     * After we create ResellerInvoice, new WHMCS invoice must be assigned to reseller
     *
     * @param $params
     */
    public function updateNewInvoices($params)
    {
        global $CONFIG;
        if(Server::isRunByCron())
        {
            $raw = new WhmcsInvoice($params["invoiceid"]);
            $whmcs = $raw->getInvoiceForReseller();

            $invoiceids = Session::get("ResellerInvoices");
            foreach($invoiceids as $invoiceid)
            {
                $invoice = new ResellersCenterInvoice($invoiceid);
                if($whmcs->userid == $invoice->userid)
                {
                    //Update resellers center invoice
                    $invoice->relinvoice_id = $params["invoiceid"];
                    if( trim(Session::get('rcChoosenGateway')) )
                    {
                        $invoice->paymentmethod = Session::get('rcChoosenGateway');
                    }
                    $invoice->save();
                    $invoice->markAsPaidIfZero();

                    //Assign created invoice to reseller
                    $whmcs->userid = $invoice->reseller->client->id;
                    $whmcs->save();
                    if(!empty($CONFIG['StoreClientDataSnapshotOnInvoiceCreation']))
                    {
                        $whmcs->refreshSnaphot();
                    }
                    break;
                }
            }
        }
        else
        {
            $invoiceid = Session::get("ResellerInvoices")[0];

            if($invoiceid)
            {
                //Update resellers center invoice
                $invoice = new ResellersCenterInvoice($invoiceid);
                $invoice->relinvoice_id = $params["invoiceid"];

                if( trim(Session::get('rcChoosenGateway')) )
                {
                    $invoice->paymentmethod = Session::get('rcChoosenGateway');
                }

                $invoice->save();
                $invoice->markAsPaidIfZero();

                //Assign created invoice to reseller
                $whmcs = new WhmcsInvoice($params["invoiceid"]);
                $whmcs->userid = $invoice->reseller->client->id;
                $whmcs->save();
                if(!empty($CONFIG['StoreClientDataSnapshotOnInvoiceCreation']))
                {
                    $whmcs->refreshSnaphot();
                }
            }
        }

        Session::clear('rcChoosenGateway');

        return $params;
    }
}

/**
 * Capture masspay reseller invoices
 * Unfortunate WHMCS does not have hook for that so it have to be done in this way
 */

if(Request::get("action") == "masspay" && Request::get("geninvoice") == "true")
{
    $reseller = ResellerHelper::getCurrent();
    if($reseller->exists && $reseller->settings->admin->resellerInvoice)
    {
        //Get Invoices to merge
        $invoices = array();
        $rcInvoicesIds = Request::get("invoiceids");
        foreach($rcInvoicesIds as $rcInvoiceId) 
        {
            $invoices[$rcInvoiceId] = new ResellersCenterInvoice($rcInvoiceId);
        }

        //Check if items are the same - otherwise we have to generate new invoice
        $repo = new RCInvoicesRepo();
        $model = $repo->getMergeInvoice(Session::get("uid"));

        $mergeInvoice = new ResellersCenterInvoice($model);
        if($mergeInvoice->status == RCInvoicesRepo::STATUS_UNPAID)
        {
            //Check if we don't miss any invoice
            foreach($mergeInvoice->items as $item)
            {
                if(array_key_exists($item->relid, $invoices) === false || $mergeInvoice->items->count() != count($invoices))
                {
                    $mergeInvoice->updateStatus(RCInvoicesRepo::STATUS_CANCELLED);
                    break;
                }
            }
        }

        //Check if we can create new merge invoice
        if(in_array($mergeInvoice->status, [RCInvoicesRepo::STATUS_PAID, RCInvoicesRepo::STATUS_CANCELLED]) || $mergeInvoice->status == null)
        {
            $client = $reseller->clients->find(Session::get("uid"))->whmcsClient;

            $mergeInvoice = new ResellersCenterInvoice();
            $mergeInvoice->create($reseller->id, $reseller->settings->getNextInvoiceNumber(), $client->id, date("Y-m-d"), date("Y-m-d"), RCInvoicesRepo::STATUS_UNPAID, Request::get("paymentmethod"), $client->tax->taxrate, $client->tax2->taxrate);

            //Create Items
            foreach($invoices as $invoice)
            {
                $invoicenum = $invoice->invoicenum ?: $invoice->id;
                $amount     = $invoice->total - $invoice->amountpaid - $invoice->credits;

                $item = new Item();
                $item->create($reseller->id, $mergeInvoice->id, $mergeInvoice->userid, InvoiceItems::TYPE_INVOICE, $invoice->id, "Invoice #{$invoicenum}", $amount, 0, date("Y-m-d"), Request::get("paymentmethod"));
            }

            //Refresh totals
            $mergeInvoice->updateInvoiceTotals();
        }

        $domain = Server::get(Configuration::getCGIHostnameVariableName());
        $path = str_replace(basename(Server::get("SCRIPT_NAME")), "", Server::get("SCRIPT_NAME"));
        $request = array("id" => $mergeInvoice->id, "gateway" => Request::get('paymentmethod'));
        Redirect::to($domain, "{$path}viewinvoice.php", $request);
    }
}