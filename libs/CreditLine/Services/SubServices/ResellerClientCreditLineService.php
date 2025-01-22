<?php

namespace MGModule\ResellersCenter\libs\CreditLine\Services\SubServices;

use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\gateways\DeferredPayments\DeferredPayments;
use MGModule\ResellersCenter\libs\CreditLine\Exceptions\ClientLimitExceedException;
use MGModule\ResellersCenter\libs\CreditLine\Exceptions\NoAvailableCreditLineException;
use MGModule\ResellersCenter\libs\CreditLine\Exceptions\ResellerLimitExceedException;
use MGModule\ResellersCenter\libs\CreditLine\Exceptions\WrongPaymentMethodException;
use MGModule\ResellersCenter\libs\CreditLine\Helpers\CreditLineOperationLogger;
use MGModule\ResellersCenter\libs\CreditLine\Interfaces\InvoiceModelInterface as Invoice;
use MGModule\ResellersCenter\repository\CreditLines;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller as ResellerObj;

class ResellerClientCreditLineService extends SubServiceInterface
{
    public function checkUserSettings($userId): bool
    {
        $reseller = ResellerHelper::getResellerObjectByHisClientId($userId);
        return $reseller->settings->admin->allowcreditline == 'on';
    }

    public function checkWhmcsCreditsAvailable(Invoice $invoice): bool
    {
        return false;
    }

    public function addCredit($invoice):bool
    {
        try {
            if ($this->isAddCreditAvailable($invoice)) {
                $creditLineRepo = new CreditLines();
                $creditLine = $creditLineRepo->getByClientId($invoice->userid);

                if (!$creditLine->exists) {
                    $params['client_id'] = $invoice->userid;
                    $params['reseller_id'] = $invoice->client->resellerClient->reseller->id;
                    $params['limit'] = 0;
                    $params['usage'] = 0;
                    $creditLineRepo->updateOrCreate($params);
                    $creditLine = $creditLineRepo->getByClientId($invoice->userid);
                }

                $creditLine->usage += abs($invoice->total);
                $creditLine->save();
                foreach ($invoice->items as $item) {
                    CreditLineOperationLogger::logAddCredit($item, $creditLine);
                }
                return true;
            } else {
                throw new \Exception("No credit line available");
            }
        } catch (\Exception $exception) {
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

        $resellerModel = $invoice->reseller;
        $reseller = new ResellerObj($resellerModel);

        if ($reseller->settings->admin->resellerInvoice && $reseller->settings->admin->checkResellerAlso) {

            //TO DO cena do nadpisania
            $invoice->userid = $reseller->client->id;
            try {
                $resellerCreditLineService = new ResellerCreditLineService();
                $resellerCreditLineService->checkIsAddCreditPossible($invoice);
            } catch (ClientLimitExceedException $e) {
                throw new ResellerLimitExceedException();
            }
        }
    }
}