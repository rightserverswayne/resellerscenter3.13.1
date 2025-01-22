<?php

namespace MGModule\ResellersCenter\mgLibs\models\Invoice;

use \WHMCS\Invoice;
use \WHMCS\PDF;

class WhmcsInvoiceExtended extends Invoice
{
    public function setTitle($title)
    {
        if ($this->pdf instanceof PDF) {
            $this->pdf->setTitle($title);
        }
    }

    public function getTemplateVars()
    {
        return $this->pdf->templateVars;
    }
}