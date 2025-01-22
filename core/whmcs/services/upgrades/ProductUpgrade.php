<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Services\Upgrades;

use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\Core\Whmcs\Services\AbstractService;
use MGModule\ResellersCenter\models\whmcs\InvoiceItem;

class ProductUpgrade extends AbstractService
{

    protected function getProductRelid()
    {
        return $this->relid;
    }

    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\models\whmcs\Upgrade::class;
    }

    public function activateOrder():bool
    {
        if (!function_exists("doUpgrade")) {
            require_once ROOTDIR . "/includes/upgradefunctions.php";
        }
        try {
            doUpgrade($this->id);
            return parent::activateOrder();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function makePayment(InvoiceItem $invoiceItem)
    {
        $newpackageid = "";
        $newbillingcycle = "";

        if (!function_exists("processUpgradePayment")) {
            require require_once ROOTDIR.DS."includes".DS. "upgradefunctions.php";
        }

        if ($invoiceItem->amount >= 0) {
            processUpgradePayment($invoiceItem->relid, "", "", "true");
        }

        if (!Session::getAndClear("RC_UpgradeStartNewPeriod")) {
            return;
        }

        if (!function_exists("getInvoicePayUntilDate")) {
            require require_once ROOTDIR.DS."includes".DS. "invoicefunctions.php";
        }

        $upgrade = \WHMCS\Service\Upgrade\Upgrade::with("order", "service", "addon")->find($invoiceItem->relid);
        $newvalue = explode(",", $upgrade->newvalue);
        list($newpackageid, $newbillingcycle) = $newvalue;

        $recurringCycles = (new \WHMCS\Billing\Cycles())->getRecurringCycles();
        $newbillingcycle = $recurringCycles[$newbillingcycle];
        if (!$newbillingcycle) {
            return;
        }

        $newnextdue = getInvoicePayUntilDate(date("Y-m-d"), $newbillingcycle, true);
        $upgrade->service->nextDueDate = $newnextdue;
        $upgrade->service->nextInvoiceDate = $newnextdue;
        $upgrade->service->save();
    }

}
