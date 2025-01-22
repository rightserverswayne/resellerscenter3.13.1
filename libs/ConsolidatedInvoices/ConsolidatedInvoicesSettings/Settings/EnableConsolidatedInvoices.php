<?php

namespace MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings;

use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Exceptions\ConsolidatedSettingException;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Interfaces\SettingInterface;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\models\Group;
use MGModule\ResellersCenter\models\Reseller;
use MGModule\ResellersCenter\models\ResellerSetting;
use MGModule\ResellersCenter\models\whmcs\Invoice;
use MGModule\ResellersCenter\repository\whmcs\Invoices;

class EnableConsolidatedInvoices implements SettingInterface
{
    const NAME = 'enableConsolidatedInvoices';

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
        $resellerSettingsTable = (new ResellerSetting())->getTable();
        $invoicesTable = (new Invoice())->getTable();

        $result = Reseller::leftJoin($resellerSettingsTable, "{$resellerSettingsTable}.reseller_id", "{$resellersTable}.id")
            ->leftJoin($invoicesTable, "{$resellersTable}.client_id", "{$invoicesTable}.userid")
            ->where("{$invoicesTable}.status", Invoices::STATUS_UNPAID)
            ->where("{$resellerSettingsTable}.setting", 'resellerInvoice')
            ->where("{$resellerSettingsTable}.value", 'on')
            ->where("{$resellersTable}.group_id", $pricingGroupId)
            ->count();

        if ($result > 0) {
            throw new ConsolidatedSettingException('pricingGroupConsolidatedBlocked');
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
        return true;
    }
}