<?php

namespace MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Interfaces;

interface SettingInterface
{
    function getName():string;
    function validate($value, $data = []):bool;
    function getDefaultValue();
    function forAdminArea():bool;
    function forResellerClient():bool;
}