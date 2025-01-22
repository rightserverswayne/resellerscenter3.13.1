<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\Core\Redirect;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice as ResellersCenterInvoice;
use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\repository\Invoices;

class ShoppingCartCheckoutCompletePage
{
    public $functions;

    public function __construct()
    {
        $this->functions[10] = function ($params) {
            return $this->redirectAfterCheckout($params);
        };
    }

    public function redirectAfterCheckout($params)
    {
        $requestedPaymentsDetails = Session::getAndClear('rcPaymentDetails');

        if (empty($requestedPaymentsDetails)) {
            return null;
        }

        if (Reseller::isMakingOrderForClient()) {
            return null;
        }

        $reseller = Reseller::getCurrent();
        if (!$reseller->exists || !$reseller->settings->admin->resellerInvoice) {
            return null;
        }

        $invoice = new ResellersCenterInvoice(Session::getAndClear("ResellerInvoices")[0]);
        if (!$invoice->exists || $invoice->status == Invoices::STATUS_DRAFT) {
            return null;
        }

        Request::merge($requestedPaymentsDetails);

        $gateway = $invoice->payments->getGateway();

        try {
            if (!$gateway) {
                throw new \Exception();
            }
            $gateway->orderFormCheckout($invoice);
            Redirect::to(Server::getCurrentSystemURL(), "rcviewinvoice.php", ["id" => $invoice->id]);
        } catch(\Exception $ex) {
            //Do nothing
        }

        // reload invoice as it may already be paid
        $invoice = new ResellersCenterInvoice($invoice->id);
        //Redirect to new invoice
        $target = ($gateway && $gateway->getType() == "CC" && $invoice->status == Invoices::STATUS_UNPAID) ? "rccreditcard.php" : "rcviewinvoice.php";
        $invoiceKey = ($target == "rccreditcard.php") ? "invoiceid" : "id";
        Redirect::to(Server::getCurrentSystemURL(), $target, [$invoiceKey => $invoice->id]);
    }

}