<?php

namespace MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings;

use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Exceptions\ConsolidatedSettingException;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Interfaces\SettingInterface;

class ConsolidatedInvoicesGenerationDay implements SettingInterface
{
    const NAME = 'consolidatedInvoicesDay';

    function getName(): string
    {
        return self::NAME;
    }

    function validate($value, $data = []): bool
    {
        if ($value < 1 || $value > 31) {
            throw new ConsolidatedSettingException('dayOfMonthMustBeInRange');
        }
        return true;
    }

    function getDefaultValue()
    {
        return 1;
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