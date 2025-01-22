<?php

namespace MGModule\ResellersCenter\libs\CreditLine\Services;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\libs\CreditLine\Exceptions\AddPaymentException;
use MGModule\ResellersCenter\libs\CreditLine\Helpers\OrderActivator;
use MGModule\ResellersCenter\libs\CreditLine\Interfaces\InvoiceModelInterface as Invoice;
use MGModule\ResellersCenter\libs\CreditLine\Helpers\CreditLineOperationLogger;
use MGModule\ResellersCenter\libs\CreditLine\Services\SubServices\ResellerClientCreditLineService;
use MGModule\ResellersCenter\libs\CreditLine\Services\SubServices\ResellerCreditLineService;
use MGModule\ResellersCenter\libs\CreditLine\Services\SubServices\SubServiceInterface;
use MGModule\ResellersCenter\repository\CreditLineHistories;

class CreditLineService
{
    const ORDER_ACTIVATED_BY_CREDIT_LINE_FLAG = 'RC_orderActivatedByCreditLine';

    public function addCreditAndActiveOrder(Invoice $invoice):bool
    {
        $service = $this->getServiceByUserId($invoice->userid);
        if (!$service->addCredit($invoice)) {
            return false;
        }

        try {
            OrderActivator::activeOrderByInvoice($invoice);
            $service->makeInvoicePayment($invoice);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function addPayment(Invoice $invoice, $allowUnrelatedPayments = false):bool
    {
        $creditHistoriesRepo = new CreditLineHistories();
        $service = $this->getServiceByUserId($invoice->userid);
        $creditLine = $service->getEnableCreditLine($invoice->userid);

        if (!$creditLine->exists) {
            return false;
        }

        try {
            foreach ($invoice->items as $item) {
                if ($creditHistoriesRepo->hasUnpaidCreditForInvoiceItem($item) || $allowUnrelatedPayments) {
                    $creditLine->usage -= $allowUnrelatedPayments ? abs($item->amount) : $item->amount;
                    CreditLineOperationLogger::logAddPayment($item, $creditLine);
                }
            }
            $creditLine->save();
            return true;
        } catch (\Exception $exception) {
            throw new AddPaymentException($exception->getMessage());
        }
    }

    public function getEnableCreditLine($userId)
    {
        $service = $this->getServiceByUserId($userId);
        return $service->getEnableCreditLine($userId);
    }

    public function getServiceByUserId($userId):SubServiceInterface
    {
        return Reseller::isReseller($userId) ? new ResellerCreditLineService() : new ResellerClientCreditLineService();
    }
}