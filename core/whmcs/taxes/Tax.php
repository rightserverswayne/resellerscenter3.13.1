<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Taxes;

use MGModule\ResellersCenter\Core\Whmcs\WhmcsObject;

/**
 * Description of Tax
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Tax extends WhmcsObject
{
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\models\whmcs\Tax::class;
    }
}