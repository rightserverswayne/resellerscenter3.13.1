<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\libs\CreditLine\Services\CreditLineService;
use MGModule\ResellersCenter\repository\CreditLineHistories;
use MGModule\ResellersCenter\repository\whmcs\Invoices;

class InvoiceCancelled
{
    public $functions;

    /**
     * Assign anonymous function
     */
    public function __construct()
    {
        $this->functions[10] = function($params) {
            return $this->giveBackCreditLimit($params);
        };
    }

    public function giveBackCreditLimit($params)
    {
        $invoices = new Invoices();
        $invoice = $invoices->find($params["invoiceid"]);
        $creditLineService = new CreditLineService();
        if ($invoice->exists) {
            $creditLineService->addPayment($invoice);

            $creditHistoriesRepo = new CreditLineHistories();
            $creditHistoriesRepo->removeInvoiceLogs($invoice);
        }

        return $params;
    }
}