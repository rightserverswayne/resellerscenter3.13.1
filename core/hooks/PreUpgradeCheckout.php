<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\Product;
use MGModule\ResellersCenter\Core\Whmcs\Products\Upgrades\Upgrade;
use MGModule\ResellersCenter\Core\Whmcs\Services\Hosting\Hosting;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\Services\ConsolidatedInvoiceService;
use MGModule\ResellersCenter\libs\CreditLine\Services\CreditLineService;
use MGModule\ResellersCenter\repository\whmcs\InvoiceItems as WhmcsInvoiceItemsRepo;
use MGModule\ResellersCenter\repository\whmcs\Invoices as WhmcsInvoicesRepo;
use MGModule\ResellersCenter\repository\Invoices as RcInvoicesRepo;
use MGModule\ResellersCenter\repository\InvoiceItems as RcInvoiceItemsRepo;
use MGModule\ResellersCenter\repository\whmcs\Upgrades;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;

class PreUpgradeCheckout
{
    public $functions;
    public static $params;

    public function __construct()
    {
        $this->functions[10] = function($params)
        {
            self::$params = $this->calculatePrices($params);
        };

        $this->functions[20] = function($params)
        {
           self::$params = $this->useConsolidatedInvoiceOnDowngrade(self::$params);
        };

        $this->functions[30] = function($params)
        {
            global $smartyvalues;
            $smartyvalues = self::$params;

            return self::$params;
        };
    }

    public function calculatePrices($params)
    {
        //Collecting negative items
        $repo = new Upgrades();
        $upgradeModel = $repo->find($params["upgradeId"]);
        $hosting = $upgradeModel->hosting;
        $isResellerOwnInvoice = false;

        if (ResellerHelper::isReseller($hosting->userid)) {
            $reseller = ResellerHelper::getLogged();
            if (!$reseller->settings->admin->resellerInvoice) {
                return $params;
            }
            $isResellerOwnInvoice = true;
            $_SESSION["RC_preventRedirectAfterUpgrade"] = true;
        } else {
            $reseller = new Reseller($hosting->resellerService->reseller->id);
        }

        if (!$reseller->exists) {
            return $params;
        }

        $newValue = explode(",", $upgradeModel->newvalue);
        $newProductId = $newValue[0];
        $newBillingCycle = $newValue[1];

        $hostingService = new Hosting($hosting->id);

        $newProduct = new Product($newProductId, $reseller);
        $upgradeService = $hostingService->getUpgrade($newProduct, $newBillingCycle);

        $currency   = new Currency($reseller->client->currency);
        $upgrade    = new Upgrade($params["upgradeId"], $reseller);
        $pricing = $upgrade->getPricing($currency);

        if (Session::get("RC_UpgradeStartNewPeriod")) {
            $pricing->setStartNewPeriodFlag();
            $upgradeService->setStartNewPeriodFlag();
        }

        if (Request::get("type") == "configoptions") {
            //CO not supported
            $adminPrice = $resellerPrice = $params["amount"];
        } else {
            $adminPrice = $pricing->getAdminPrice();
            $resellerPrice = $upgradeService->getPrice();
        }

        $params['reseller'] = $reseller;
        $params['hosting'] = $hosting;
        $params['adminPrice'] = $adminPrice;
        $params['resellerPrice'] = $resellerPrice;
        $params['isResellerOwnInvoice'] = $isResellerOwnInvoice;
        $params['itemNextDueDate'] = $upgradeService->getNextDueDate();
        $params['itemDescription'] = $upgradeService->getDescription();
        $params['originalPrice'] = $params["amount"];
        $params["amount"] = $isResellerOwnInvoice ? $params["amount"] : $resellerPrice;

        return $params;
    }

    public function useConsolidatedInvoiceOnDowngrade($params)
    {
        $reseller = $params['reseller'];
        if (empty($params['reseller']) || !$reseller->exists) {
            return $params;
        }

        $adminPrice = $params['adminPrice'];
        $resellerPrice = $params['resellerPrice'];
        $hosting = $params['hosting'];
        $isResellerOwnInvoice = $params['isResellerOwnInvoice'];

        $resellerInvoices = !$isResellerOwnInvoice && $reseller->settings->admin->resellerInvoice;

        if ($resellerPrice >= 0) {
            return $params;
        }

        $invoicesRepo = new WhmcsInvoicesRepo();
        $dateTime = new \DateTime('NOW');
        $client = new Client($hosting->userid);

        $parameters = [
            'userid' => $resellerInvoices ? $reseller->client->id : $client->id,
            'subtotal' => $resellerInvoices ? $adminPrice : $resellerPrice,
            'total' => $resellerInvoices ? $adminPrice : $resellerPrice,
            'paymentmethod' => 'banktransfer',
            'date' => $dateTime->format("Y-m-d"),
            'duedate' => $params['itemNextDueDate'],
            'status' => WhmcsInvoicesRepo::STATUS_UNPAID
        ];

        $dumpInvoice = $invoicesRepo->create($parameters);

        $upgradeInvoiceItemData = [
            'invoiceid' => $dumpInvoice->id,
            'userid' => $dumpInvoice->userid,
            'type' => WhmcsInvoiceItemsRepo::TYPE_UPGRADE,
            'relid' => $params["upgradeId"],
            'description' => $params['itemDescription'],
            'amount' => $resellerInvoices ? $adminPrice : $resellerPrice,
            'date' => $dateTime->format("Y-m-d"),
            'duedate' => $params['itemNextDueDate']
        ];

        $itemsRepo = new WhmcsInvoiceItemsRepo();
        $whmcsInvoiceItem = $itemsRepo->create($upgradeInvoiceItemData);

        $consolidatedService = new ConsolidatedInvoiceService();
        $creditLineService = new CreditLineService();

        if ($consolidatedService->check($dumpInvoice)) {
            $consolidatedService->mergeWhmcsInvoice($dumpInvoice);
            $creditLineService->addPayment($dumpInvoice, true);
            $params["amount"] = 0;
        } else {
            $whmcsInvoiceItem->delete();
            $dumpInvoice->delete();
        }

        if (!$resellerInvoices) {
            return $params;
        }

        $rcInvoicesRepo = new RcInvoicesRepo();

        $parameters['userid'] = $client->id;
        $parameters['subtotal'] = $resellerPrice;
        $parameters['total'] = $resellerPrice;
        $parameters['reseller_id'] = $reseller->id;
        $parameters['invoicenum'] = $reseller->settings->getNextInvoiceNumber();

        $rcDumpInvoice = $rcInvoicesRepo->create($parameters);

        $upgradeInvoiceItemData['reseller_id'] = $reseller->id;
        $upgradeInvoiceItemData['invoice_id'] = $rcDumpInvoice->id;
        $upgradeInvoiceItemData['userid'] = $rcDumpInvoice->userid;
        $upgradeInvoiceItemData['type'] = RcInvoiceItemsRepo::TYPE_UPGRADE;
        $upgradeInvoiceItemData['relid'] = $params["upgradeId"];
        $upgradeInvoiceItemData['amount'] = $resellerPrice;

        $itemsRepo = new RcInvoiceItemsRepo();
        $rcDumpInvoiceItem = $itemsRepo->create($upgradeInvoiceItemData);

        if ($consolidatedService->checkRcInvoice($rcDumpInvoice)) {
            $consolidatedService->mergeRcInvoice($rcDumpInvoice);
            $creditLineService->addPayment($rcDumpInvoice, true);
        } else {
            $rcDumpInvoiceItem->delete();
            $rcDumpInvoice->delete();
        }

        return $params;
    }

}