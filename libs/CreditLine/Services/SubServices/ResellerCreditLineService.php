<?php

namespace MGModule\ResellersCenter\libs\CreditLine\Services\SubServices;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller as ResellerObj;
use MGModule\ResellersCenter\gateways\DeferredPayments\DeferredPayments;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings\EnableConsolidatedInvoices;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\libs\CreditLine\Exceptions\ClientLimitExceedException;
use MGModule\ResellersCenter\libs\CreditLine\Exceptions\NoAvailableCreditLineException;
use MGModule\ResellersCenter\libs\CreditLine\Exceptions\WrongPaymentMethodException;
use MGModule\ResellersCenter\libs\CreditLine\Helpers\CreditLineOperationLogger;
use MGModule\ResellersCenter\libs\CreditLine\Interfaces\InvoiceModelInterface as Invoice;
use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;
use MGModule\ResellersCenter\models\whmcs\Client;
use MGModule\ResellersCenter\repository\CreditLines;
use MGModule\ResellersCenter\models\Invoice as RcInvoice;

class ResellerCreditLineService extends SubServiceInterface
{
    public function checkUserSettings($userId): bool
    {
        return true;
    }

    public function checkWhmcsCreditsAvailable(Invoice $invoice): bool
    {
        $client = Client::find($invoice->userid);
        return $client->credit >= abs($invoice->total);
    }

    public function addCredit($invoice):bool
    {
        try {
            $rcInvoice = RcInvoice::where('relinvoice_id', $invoice->id)->first();
            if ($rcInvoice->exists) {
                $clientService = new ResellerClientCreditLineService();
                $clientService->addCredit($rcInvoice);
            }

            $reseller = new ResellerObj($invoice->getReseller());
            $checkWhmcsCredits = $rcInvoice->exists && !SettingsManager::getSettingFromReseller($reseller, EnableConsolidatedInvoices::NAME);
            $this->addResellerCredit($invoice, $checkWhmcsCredits);
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    protected function addResellerCredit($invoice, $checkWhmcsCredits = false)
    {
        if ($this->isAddCreditForResellerAvailable($invoice)) {
            $this->addSingleCredit($invoice);
        } elseif ($checkWhmcsCredits && $this->checkWhmcsCreditsAvailable($invoice)) {
            $result = WhmcsAPI::request("ApplyCredit", [
                'invoiceid' => $invoice->id,
                'amount' => abs($invoice->total),
            ]);

            if (!$result['invoicepaid']) {
                throw new \Exception('Add credit failed');
            }
        } else {
            throw new \Exception('Add credit failed');
        }
    }

    protected function addSingleCredit($invoice)
    {
        $creditLineRepo = new CreditLines();
        $creditLine = $creditLineRepo->getByClientId($invoice->userid);

        if (!$creditLine->limit) {
            throw new \Exception("Credit Line not exists.");
        }
        $creditLine->usage += abs($invoice->total);
        $creditLine->save();
        foreach ($invoice->items as $item) {
            CreditLineOperationLogger::logAddCredit($item, $creditLine);
        }
    }

    public function isAddCreditForResellerAvailable(Invoice $invoice):bool
    {
        if (strtolower($invoice->paymentmethod) != DeferredPayments::SYS_NAME || $invoice->credit != 0) {
            return false;
        }

        if ($creditLine = $this->getEnableCreditLine($invoice->userid)) {
            return $creditLine->limit && ($creditLine->limit - $creditLine->usage) >= abs($invoice->total);
        } else {
            return false;
        }
    }

    public function checkIsAddCreditPossible(Invoice $invoice)
    {
        $creditLine = $this->getEnableCreditLine($invoice->userid);
        if (!(bool)$creditLine->limit) {
            throw new NoAvailableCreditLineException();
        }

        if (strtolower($invoice->paymentmethod) != DeferredPayments::SYS_NAME) {
            throw new WrongPaymentMethodException();
        }

        if ($creditLine->limit - $creditLine->usage < abs($invoice->total)) {
            throw new ClientLimitExceedException();
        }
    }
}