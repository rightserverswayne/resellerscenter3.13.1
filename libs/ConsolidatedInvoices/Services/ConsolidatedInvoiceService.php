<?php

namespace MGModule\ResellersCenter\libs\ConsolidatedInvoices\Services;

use DateTime;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\core\Logger;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller as ResellerObj;
use MGModule\ResellersCenter\Helpers\InvoicePaymentHelper;
use MGModule\ResellersCenter\Helpers\ModuleConfiguration;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings\EnableConsolidatedInvoices;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings\EndClientConsolidatedInvoices;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\Helpers\ConsolidatedInvoiceHelper;
use MGModule\ResellersCenter\libs\CreditLine\Helpers\OrderActivator;
use MGModule\ResellersCenter\libs\CreditLine\Interfaces\InvoiceModelInterface as Invoice;
use MGModule\ResellersCenter\repository\Logs;
use MGModule\ResellersCenter\repository\Resellers;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\models\Invoice as RcInvoiceModel;
use MGModule\ResellersCenter\models\whmcs\Invoice as WhmcsInvoiceModel;
use MGModule\ResellersCenter\repository\ResellersClientsSettings;

class ConsolidatedInvoiceService
{
    protected $orderId;

    public function mergeWhmcsInvoice(WhmcsInvoiceModel $invoice, $activateOrderAfterMerge = true)
    {
        if (!$this->check($invoice)) {
            return false;
        }

        if ($activateOrderAfterMerge) {
            InvoicePaymentHelper::makeInvoicePayment($invoice);
            $consolidatedInvoiceId = $this->mergeInvoice($invoice);
            OrderActivator::activeOrderByInvoice($invoice);
        } else {
            $consolidatedInvoiceId = $this->mergeInvoice($invoice);
        }

        return $consolidatedInvoiceId;
    }

    public function mergeRcInvoice(RcInvoiceModel $invoice, $activateOrderAfterMerge = true)
    {
        if (!$this->checkRcInvoice($invoice)) {
            return false;
        }
        $consolidatedInvoiceId = $this->mergeInvoice($invoice);
        if ($activateOrderAfterMerge) {
            OrderActivator::activeOrderByInvoice($invoice->whmcsInvoice);
        }

        return $consolidatedInvoiceId;
    }

    protected function mergeInvoice(Invoice $invoice)
    {
        $invoice->decrementNumbering();
        $consolidateInvoice = ConsolidatedInvoiceHelper::getConsolidatedInvoiceForCurrentMonth($invoice);
        if (!$consolidateInvoice->exists()) {
            ConsolidatedInvoiceHelper::changeInvoiceToConsolidatedInvoice($invoice);
            return $invoice->id;
        } else {
            $consolidateInvoice->collectItemsFromInvoice($invoice);
            $consolidateInvoice->assignToOrder($this->orderId);
            $consolidateInvoice->recalculateInvoiceItems();
            $invoice->delete();
            return $consolidateInvoice->getId();
        }
    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    public function check(WhmcsInvoiceModel $invoice):bool
    {
        if ($invoice->credit != 0) {
            return false;
        }

        $reseller = ResellerHelper::getByClientId($invoice->userid);
        if ($reseller->exists) {
            return $reseller->settings->admin->resellerInvoice && SettingsManager::getSettingFromReseller($reseller, EnableConsolidatedInvoices::NAME) == 'on';
        }

        $reseller = ResellerHelper::getCurrent();
        if (!$reseller->exists) {
            return false;
        }

        $resellerClient = (new ResellersClients())->getByRelid($invoice->userid, null, $reseller->id);

        if (!$resellerClient->exists || SettingsManager::getSettingFromReseller($reseller, EndClientConsolidatedInvoices::NAME) != 'on') {
            return false;
        }

        $resellerClientsSettings = new ResellersClientsSettings();
        return $resellerClientsSettings->getSetting($resellerClient, EnableConsolidatedInvoices::NAME) == 'on';
    }

    public function isConsolidatedInvoice(Invoice $invoice):bool
    {
        $resellerRepo = new Resellers();
        $resellerModel = $resellerRepo->getResellerByClientId($invoice->userid);
        if ($resellerModel->exists) {
            $reseller = new ResellerObj($resellerModel);
            return SettingsManager::getSettingFromReseller($reseller, EnableConsolidatedInvoices::NAME) == 'on';
        } else {
            $resellerClient = (new ResellersClients())->getByRelid($invoice->userid);
            $reseller = new ResellerObj($resellerClient->reseller);

            if (!$resellerClient->exists || SettingsManager::getSettingFromReseller($reseller, EndClientConsolidatedInvoices::NAME) != 'on') {
                return false;
            }
            $resellerClientsSettings = new ResellersClientsSettings();
            return $resellerClientsSettings->getSetting($resellerClient, EnableConsolidatedInvoices::NAME) == 'on';
        }
    }

    public function checkRcInvoice(RcInvoiceModel $invoice)
    {
        if ($invoice->credit != 0) {
            return false;
        }

        $resellerClient = (new ResellersClients())->getByRelid($invoice->userid, null, $invoice->reseller->id);
        $reseller = new ResellerObj($invoice->reseller);
        if (!$resellerClient->exists || SettingsManager::getSettingFromReseller($reseller, EndClientConsolidatedInvoices::NAME) != 'on') {
            return false;
        }

        $resellerClientsSettings = new ResellersClientsSettings();
        return $resellerClientsSettings->getSetting($resellerClient, EnableConsolidatedInvoices::NAME) == 'on';
    }

    public function activeConsolidatedInvoices()
    {
        $moduleConfig = ModuleConfiguration::getModuleConfiguration();
        $debug = $moduleConfig['consolidated']['debug'];

        if ($debug) {
            $this->activeByModelDebugMode(new WhmcsInvoiceModel());
            $this->activeByModelDebugMode(new RcInvoiceModel());
        } else {
            $this->activeByModel(new WhmcsInvoiceModel());
            $this->activeByModel(new RcInvoiceModel());
        }
    }

    protected function activeByModel(Invoice $invoice)
    {
        $consolidatedInvoices = ConsolidatedInvoiceHelper::getConsolidatedInvoicesForActivate($invoice);
        foreach ($consolidatedInvoices as  $consolidatedInvoice) {
            $consolidatedInvoice->activate();
        }
    }

    protected function activeByModelDebugMode(Invoice $invoice)
    {
        $logger = new Logger();

        $consolidatedInvoices = ConsolidatedInvoiceHelper::getConsolidatedInvoicesForActivate($invoice);
        $date = new DateTime("NOW");

        $type = is_a($invoice, WhmcsInvoiceModel::class) ? 'WHMCS' : 'ResellerCenter';

        $previousMonthDate = ConsolidatedInvoiceHelper::generatePreviousMonthDate();

        $mess = "Publishing process. Invoice type: " . $type . "<br>
Today is: " . $date->format("Y-m-d")."<br>
Module looks for invoices with month: " .  $previousMonthDate->format('m-Y')." and older<br>
Found: " . count($consolidatedInvoices) . " Consolidated Invoices.";

        $logger->addNewLog(Logs::INFO,  $mess);

        foreach ($consolidatedInvoices as  $consolidatedInvoice) {
            $resultData = [];
            $id = $consolidatedInvoice->getId();
            $number = $consolidatedInvoice->getNumber();

            $resultData['item'] = '#' . $id . " " . $number;
            $resultData['result'] = $consolidatedInvoice->activate();

            $logger->addNewLog(Logs::INFO, json_encode($resultData));
        }
    }

}