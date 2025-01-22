<?php

namespace MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings;

use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Exceptions\ConsolidatedSettingException;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Interfaces\SettingInterface;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\models\Group;
use MGModule\ResellersCenter\models\Reseller;
use MGModule\ResellersCenter\models\ResellerService;
use MGModule\ResellersCenter\models\Invoice as RcInvoice;
use MGModule\ResellersCenter\models\InvoiceItem as RcInvoiceItem;
use MGModule\ResellersCenter\models\whmcs\Invoice as WhmcsInvoice;
use MGModule\ResellersCenter\models\whmcs\InvoiceItem as WhmcsInvoiceItem;
use MGModule\ResellersCenter\repository\whmcs\Invoices;
use MGModule\ResellersCenter\repository\Invoices as RcInvoices;
use \Illuminate\Database\Capsule\Manager as DB;

class EndClientConsolidatedInvoices implements SettingInterface
{
    const NAME = 'endClientConsolidatedInvoices';

    function getName(): string
    {
        return self::NAME;
    }

    function validate($value, $data = []): bool
    {
        $pricingGroupId = $data['pricingGroup'];
        if (!$pricingGroupId) {
            return true;
        }

        $pricingGroup = new Group();
        $settingManager = new SettingsManager();
        $setting = $settingManager->getSettingFromPricingGroup($pricingGroup->find($pricingGroupId), self::NAME);

        if ($setting == 'on') {
            return true;
        }

        $resellersTable = (new Reseller())->getTable();
        $resellerServicesTable = (new ResellerService())->getTable();

        $baseQuery = Reseller::where("{$resellersTable}.group_id", $pricingGroupId);

        //Check Reseller Invoices
        $invoicesTable = (new RcInvoice())->getTable();

        $rcQuery = clone($baseQuery);

        $rcResult = $rcQuery
            ->leftJoin($invoicesTable, "{$invoicesTable}.reseller_id", "{$resellersTable}.id")
            ->where("{$invoicesTable}.status", RcInvoices::STATUS_UNPAID)
            ->addSelect("{$invoicesTable}.id as invoiceId")
            ->count();

        if ($rcResult > 0) {
            throw new ConsolidatedSettingException('pricingGroupEndClientConsolidatedBlocked');
        }

        //Check WHMCS Invoices
        $invoicesTable = (new WhmcsInvoice())->getTable();
        $invoiceItemsTable = (new WhmcsInvoiceItem())->getTable();

        $whmcsQuery = clone($baseQuery);

        $whmscResult = $whmcsQuery
            ->leftJoin($resellerServicesTable, "{$resellerServicesTable}.reseller_id", "{$resellersTable}.id")
            ->leftJoin($invoiceItemsTable, function($join) use ($invoiceItemsTable, $resellerServicesTable) {
                $join->on("{$invoiceItemsTable}.relid", "=", "{$resellerServicesTable}.relid")
                    ->on(DB::raw("LOWER({$invoiceItemsTable}.type)"), "=", "{$resellerServicesTable}.type");
            })
            ->leftJoin($invoicesTable, function($join) use ($invoicesTable, $invoiceItemsTable, $resellersTable) {
                $join->on("{$invoicesTable}.id", "=", "{$invoiceItemsTable}.invoiceid")
                    ->on("{$invoicesTable}.userid", "!=", "{$resellersTable}.client_id");
            })
            ->where("{$invoicesTable}.status", Invoices::STATUS_UNPAID)
            ->count();

        if ($whmscResult > 0) {
            throw new ConsolidatedSettingException('pricingGroupEndClientConsolidatedBlocked');
        }

        return true;
    }

    function getDefaultValue()
    {
        return null;
    }

    function forAdminArea(): bool
    {
        return true;
    }

    function forResellerClient(): bool
    {
        return false;
    }
}