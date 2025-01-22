<?php
namespace MGModule\ResellersCenter\Core\Resources\Pages\Invoices;

use \MGModule\ResellersCenter\Core\Resources\Invoices\Invoice as ResellersCenterInvoice;
/**
 * Description of Page.php
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Invoice
{
    public function getView(ResellersCenterInvoice $invoice, $extra = [])
    {
        $decorator = new Decorator();
        return $decorator->getPageView($invoice, $extra);
    }
}