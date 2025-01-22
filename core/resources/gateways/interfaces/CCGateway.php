<?php
namespace MGModule\ResellersCenter\Core\Resources\Gateways\Interfaces;

/**
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
interface CCGateway 
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
     * Capture payment 
     * Called when a payment is to be processed and captured.
     * 
     * @param \MGModule\ResellersCenter\Core\Resources\Invoices\Invoice $invoice
     * @return array Response status
     */
    public function capture($params);
    
    /**
     * Some gateways required additional js libs in cart page
     * 
     * @return js script / library
     */
    public function getHeadOutput();
}
