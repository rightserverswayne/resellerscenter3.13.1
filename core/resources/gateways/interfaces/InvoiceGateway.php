<?php
namespace MGModule\ResellersCenter\core\resources\gateways\interfaces;

/**
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
interface InvoiceGateway
{
    /**
     * Payment link. 
     * 
     * Defines the HTML output displayed on an invoice. Typically consists of an
     * HTML form that will take the user to the payment gateway endpoint.
     * 
     * @param \MGModule\ResellersCenter\Core\Resources\Invoices\Invoice $invoice
     * @return string
     */
    public function link(\MGModule\ResellersCenter\Core\Resources\Invoices\Invoice $invoice);

    /**
     * Payment callback.
     * Here should be code that will handle payment gateway callback within WHMCS.
     */
    public function callback($data);
    
    /**
     * Refund transaction.
     * Called when a refund is requested for a previously successful transaction.
     * 
     * @param $params
     * @return array Response status
     */
    public function refund($params);
}
