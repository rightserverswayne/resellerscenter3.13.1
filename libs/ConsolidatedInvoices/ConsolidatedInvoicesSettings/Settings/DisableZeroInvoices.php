<?php

namespace MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings;

use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Interfaces\SettingInterface;

class DisableZeroInvoices implements SettingInterface
{
    const NAME = 'disableZeroConsolidated';

    function getName(): string
    {
        return self::NAME;
    }

    function validate($value, $data = []): bool
    {
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