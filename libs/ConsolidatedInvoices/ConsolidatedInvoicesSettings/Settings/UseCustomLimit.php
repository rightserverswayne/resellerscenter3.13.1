<?php

namespace MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings;

use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Interfaces\SettingInterface;

class UseCustomLimit implements SettingInterface
{
    const NAME = 'useCustomCreditLineLimit';

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
        return false;
    }

    function forAdminArea(): bool
    {
        return false;
    }

    function forResellerClient(): bool
    {
        return true;
    }
}