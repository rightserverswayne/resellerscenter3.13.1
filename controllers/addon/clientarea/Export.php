<?php

namespace MGModule\ResellersCenter\controllers\addon\clientarea;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Helpers\ExportCSV;
use MGModule\ResellersCenter\Helpers\ExportDataHelper;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;

class Export extends AbstractController
{

    public function processExportDataHTML()
    {
        $dataType   = Request::get("dataType");
        $reseller = Reseller::getLogged();

        if($dataType == 'Invoices')
        {
            $dataType = $this->getInvoiceDataType($reseller);
        }

        if($dataType == 'Transactions')
        {
            $dataType = $this->getTransactionsDataType($reseller);
        }

        $export = new ExportCSV($this->getClassForDataType($dataType), $reseller->id);
        $export->download();
    }

    private function getClassForDataType($dataTypeName)
    {
        $helper = new ExportDataHelper();
        return $helper->getExportDataTypes()[$dataTypeName];
    }

    private function getInvoiceDataType($reseller)
    {
        return $reseller->settings->admin->resellerInvoice ? 'Resellers Center Invoices' : 'WHMCS Invoices';
    }

    private function getTransactionsDataType($reseller)
    {
        return $reseller->settings->admin->resellerInvoice ? 'Resellers Center Transactions' : 'WHMCS Transactions';
    }

}