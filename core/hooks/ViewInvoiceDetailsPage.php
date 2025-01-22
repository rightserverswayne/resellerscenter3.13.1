<?php
namespace MGModule\ResellersCenter\core\hooks;


use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\repository\whmcs\Invoices;

/**
 * Description of ViewInvoiceDetailsPage
 *
 * @author Paweł Złamaniec
 */
class ViewInvoiceDetailsPage
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     *
     * @var array
     */
    public $functions;

    /**
     * Container for hook params
     *
     * @var type
     */
    public static $params;

    /**
     * Assign anonymous function
     */
    public function __construct()
    {
        $this->functions[10] = function($params)
        {
            return $this->brandInvoice($params);
        };
    }

    /**
     * @param $params
     * @return mixed
     */
    public function brandInvoice($params)
    {
        global $whmcs;

        $reseller = ResellerHelper::getByCurrentURL();
        if(!$reseller->exists || !$reseller->settings->admin->invoiceBranding)
        {
            return $params;
        }

        $repo = new Invoices();
        $invoice = $repo->find($params["invoiceid"]);

        $params["payto"] = html_entity_decode(nl2br($reseller->settings->private->payto));
        if(!empty($invoice->branded->invoicenum))
        {
            $params["pagetitle"] = $whmcs->get_lang("invoicenumber") . $invoice->branded->invoicenum;
            $params["invoicenum"] = $invoice->branded->invoicenum;
        }
    }

}