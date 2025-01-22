<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\Core\Resources\Invoices\Pdf;
use MGModule\ResellersCenter\Loader;
use MGModule\ResellersCenter\models\whmcs\Invoice;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\repository\ResellersSettings;
use MGModule\ResellersCenter\repository\whmcs\Invoices;
use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice as ResellersCenterInvoice;

use MGModule\ResellersCenter\core\Redirect;
use MGModule\ResellersCenter\core\Session;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\core\Server;

use MGModule\ResellersCenter\core\helpers\ClientAreaHelper;
use MGModule\ResellersCenter\repository\whmcs\Invoices as WhmcsInvoices;

/**
 * Description of InvoiceDownload
 *
 * @author Paweł Złamaniec
 */
class InvoiceDownload 
{
    public $functions;

    public function __construct()
    {
        $this->functions = [];
    }
    /**
     * There is not hook for invoice download
     * 'Hook' is implemented below
     */
}

//Capture invoice download and swap files
if( basename(Server::get("SCRIPT_NAME")) == 'dl.php' && Request::get("type") == 'i' && !empty(Request::get("id")))
{
    $reseller = Reseller::getCurrent();
    if ( $reseller->exists) {
        global $whmcs;
        $invoiceid = Request::get("id");

        if($reseller->settings->admin->resellerInvoice)
        {
            if(!$reseller->settings->admin->disableEndClientInvoices)
            {
                $invoice = new ResellersCenterInvoice($invoiceid);
                $invoice->invoicenum = $invoice->invoicenum != "" ? $invoice->invoicenum : $invoice->id;

                $data = $invoice->pdf->getFile();
            }
        }
        else
        {
            $repo = new Invoices();
            $invoice = $repo->find($invoiceid);
            $invoice->invoicenum = $invoice->branded->invoicenum ?: $invoice->invoicenum ?: $invoice->id;

            $whmcsInvoice = new \WHMCS\Invoice($invoice->id);
            $whmcsInvoice->pdfCreate();
            $whmcsInvoice->pdfInvoicePage($invoice->id);
            $data = $whmcsInvoice->pdfOutput();
        }

        //Check if invoice belongs to the current client - if not redirect to viewinvoice.php and require login or display whmcs error alert
        if($invoice->userid != Session::get("uid") && Session::get("adminid"))
        {
            Redirect::toPageWithQuery("viewinvoice.php", [
                "id" => Request::get("id")
            ]);
        }

        //Generate filename
        $filename = $whmcs->get_lang("invoicefilename") . $invoice->invoicenum . ".pdf";

        header("Content-type:application/pdf");
        header('Content-Disposition:attachment;filename="'.$filename.'"');
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . strlen($data));
        echo $data;
        exit;
    }

}
