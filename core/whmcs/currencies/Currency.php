<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Currencies;

use MGModule\ResellersCenter\Core\Whmcs\WhmcsObject;

/**
 * Description of Currency
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Currency extends WhmcsObject
{
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\Models\Whmcs\Currency::class;
    }

    public function getModel()
    {
        if(empty($this->model))
        {
            $this->load();
        }

        return $this->model;
    }
}