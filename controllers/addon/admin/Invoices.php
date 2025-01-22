<?php
namespace MGModule\ResellersCenter\Controllers\Addon\Admin;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;

use MGModule\ResellersCenter\repository\Invoices as InvoicesRepo;
use MGModule\ResellersCenter\repository\whmcs\Invoices as WhmcsInvoices;

use MGModule\ResellersCenter\repository\Transactions;
use MGModule\ResellersCenter\repository\whmcs\Transactions as WHMCSTransactions;

use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice;
use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\core\datatable\DatatableDecorator as Datatable;
use MGModule\ResellersCenter\mgLibs\Lang;

use MGModule\ResellersCenter\core\Request;
/**
 * Description of Invoice
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Invoices extends AbstractController
{
    public function getRcInvoicesForTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();
        $resellerid = Request::get("resellerid");
        
        $invoices = new InvoicesRepo();
        $result = $invoices->getInvoicesForTable($resellerid, $dtRequest);
        
        $format = [
            "client"    => ["link" => ["userid", "client"]],
            "status"    => [
                "class" => [["Paid", "text-success"], ["Unpaid", "text-danger"]],
                "lang" => ["table", "paymentstatus"]
            ]
        ];

        $datatable = new Datatable($format, $this->getButtonsForInvoiceTable());
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }
    
    public function getWHMCSInvoicesForTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();
        $resellerid = Request::get("resellerid");

        $invoices = new WhmcsInvoices();
        $result = $invoices->getInvoicesForTable($resellerid, $dtRequest);
        
        $format = [
            "invoicenum"=> ["link" => ["id", "invoice"]],
            "client"    => ["link" => ["userid", "client"]],
            "status"    => [
                "class" => [["Paid", "text-success"], ["Unpaid", "text-danger"]],
                "lang" => ["table", "paymentstatus"]
            ]
        ];
        
        $datatable = new Datatable($format, $this->getButtonsForInvoiceTable());
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }
    
    public function getRCTransactionsForTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();
        $resellerid = Request::get("resellerid");
        
        $transactions = new Transactions();
        $result = $transactions->getForTable($dtRequest, null, $resellerid);

        $format = array(
            "id"        => array("link" => array("id", "transaction")),
            "client"    => array("link" => array("client_id", "client")),
        );
        
        $buttons = array(
            array(
                "type" => "only-icon", 
                "class" => "openTransactionEdit btn-primary", 
                "data" => array("transactionid" => "id"), 
                "icon" => "fa fa-edit",
                "tooltip" => Lang::T('table','detailsTooltip')
            )
        );
        
        $datatable = new Datatable($format, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }
    
    public function getWHMCSTransactionsForTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();
        $resellerid = Request::get("resellerid");
        
        $transactions = new WHMCSTransactions();
        $result = $transactions->getTransactionsForTable($resellerid, $dtRequest);

        $format = array(
            "id"        => array("link" => array("id", "transaction")),
            "client"    => array("link" => array("client_id", "client")),
        );
        
        $buttons = array(
            array(
                "type" => "only-icon", 
                "class" => "openTransactionEdit btn-primary", 
                "data" => array("transactionid" => "id"), 
                "icon" => "fa fa-edit",
                "tooltip" => Lang::T('table','detailsTooltip')
            )
        );
        
        $datatable = new Datatable($format, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }
    
    /**
     * Get invoice items 
     * 
     * @since 3.0.0
     * @return type
     */
    public function getRCInvoiceDetailsJSON()
    {
        $invoiceid = Request::get("invoiceid");
        $invoice = new Invoice($invoiceid);
        
        return array(
            "invoice" => $invoice->toArray(),
            "items" => $invoice->items->toArray()
        );
    }
    
    /**
     * Edit invoice items
     * 
     * @since 3.0.0
     * @return type
     */
    public function updateRCInvoiceJSON()
    {
        $data = Request::get("invoice");
        $invoice = new Invoice($data["invoiceid"]);
        
        $invoice->update($data);
        
        EventManager::call("invoiceUpdated", $invoice);
        return array("success" => Lang::T('update','success'));
    }
    
    private function getButtonsForInvoiceTable()
    {
        return array(
            array(
                "type" => "only-icon", 
                "class" => "openInvoiceEdit btn-primary", 
                "data" => array("invoiceid" => "id"), 
                "icon" => "fa fa-edit",
                "tooltip" => Lang::T('table','detailsTooltip')
            ),    
        );
    }
}
