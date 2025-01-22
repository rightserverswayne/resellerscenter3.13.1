<?php

/* * ********************************************************************
 * MGMF product developed. (2016-02-23)
 * *
 *
 *  CREATED BY MODULESGARDEN       ->       http://modulesgarden.com
 *  CONTACT                        ->       contact@modulesgarden.com
 *
 *
 * This software is furnished under a license and may be used and copied
 * only  in  accordance  with  the  terms  of such  license and with the
 * inclusion of the above copyright notice.  This software  or any other
 * copies thereof may not be provided or otherwise made available to any
 * other person.  No title to and  ownership of the  software is  hereby
 * transferred.
 *
 *
 * ******************************************************************** */

namespace MGModule\ResellersCenter\controllers\addon\clientarea;

use MGModule\ResellersCenter\Core\Helpers\Files;
use MGModule\ResellersCenter\Core\Helpers\InvoiceDateHelper;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\Core\Resources\Invoices\Item;
use MGModule\ResellersCenter\Core\Resources\Invoices\Pdf;
use MGModule\ResellersCenter\libs\CreditLine\Services\CreditLineService;
use MGModule\ResellersCenter\Loader;
use MGModule\ResellersCenter\mgLibs\models\Invoice\WhmcsInvoiceExtended;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;

use MGModule\ResellersCenter\repository\Invoices as RCInvoicesRepo;
use MGModule\ResellersCenter\repository\Transactions as TransactionsRepo;
use MGModule\ResellersCenter\repository\PaymentGateways;
use MGModule\ResellersCenter\models\PaymentGateway as PaymentGatewayModel;
use MGModule\ResellersCenter\repository\whmcs\Invoices as WhmcsInvoices;

use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice;
use MGModule\ResellersCenter\core\datatable\DatatableDecorator as Datatable;

use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\repository\whmcs\Clients as WhmcsClientsRepo;
use MGModule\ResellersCenter\core\resources\gateways\Factory as PaymentGatewayFactory;

/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Invoices extends AbstractController 
{
    public function indexHTML()
    {
        $reseller = Reseller::getLogged();
        
        return array(
            'tpl'   => 'base',
            'vars' => array(
                "reseller" => $reseller,
                "gateways" => Helper::getEnabledCustomGateways($reseller->id)
            )
        );
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getCreateInvoiceDataJSON()
    {
        $invoice = Request::get('invoice');
        $reseller = Reseller::getLogged();
        $userId = !empty($invoice) ? $invoice['userid'] : null;

        $invoiceDates = InvoiceDateHelper::generateDefaultInvoiceDates();

        $result["invoicenum"] = $invoice["invoicenum"]  ?: $reseller->settings->getNextInvoiceNumber(false);
        $result["date"] = $invoice["date"]  ?: $invoiceDates['date'];
        $result["duedate"] = $invoice["duedate"]  ?: $invoiceDates['duedate'];

        if (!$userId) {
            return $result;
        }

        if (!function_exists("getTaxRate")) {
            require_once Files::getWhmcsPath("includes","invoicefunctions.php");
        }
        $clientsRepo = new WhmcsClientsRepo();
        $client = $clientsRepo->find($userId);
        $taxState = $client->state;
        $taxCountry = $client->country;

        $taxLevel1 = getTaxRate(1, $taxState, $taxCountry);
        $taxRate1 = $taxLevel1["rate"];
        $taxLevel2 = getTaxRate(2, $taxState, $taxCountry);
        $taxRate2 = $taxLevel2["rate"];
        $result["tax1"] = $taxRate1;
        $result["tax2"] = $taxRate2;
        $result["itemTaxed"] = !empty($taxRate1) || !empty($taxRate2);

        return $result;
    }

    /**
     * Get invoice items 
     * 
     * @since 3.0.0
     * @return type
     */
    public function getInvoiceDetailsJSON()
    {
        $invoiceid = Request::get("invoiceid");
        $type      = Request::get("type");
        
        //Reseller Center invoices
        if ($type == 'rc') {
            $invoice = new Invoice($invoiceid);
            $gateway = PaymentGatewayFactory::get($invoice->reseller->id, $invoice->paymentmethod);
            $invoice->paymentmethod = $gateway->adminName ?: ucfirst($invoice->paymentmethod);
        } else {
            $invoices = new WhmcsInvoices();
            $invoice = $invoices->find($invoiceid);
            try {
                $gateway = \WHMCS\Module\Gateway::factory($invoice->paymentmethod);
                $invoice->paymentmethod = $gateway->getDisplayName();
            } catch (\Exception $e) {
                $invoice->paymentmethod = ucfirst($invoice->paymentmethod);
            }
        }

        $invoiceData = $invoice->toArray();
        $invoiceData['statusTranslated'] = Lang::absoluteT('invoices', 'paymentstatus', $invoice->status);

        return array(
            "invoice" => $invoiceData,
            "items" => $invoice->items->toArray(),
            "currency" => $invoice->client->currencyObj->toArray(),
            "amounttopay" => $invoice->total - $invoice->amountpaid,
            "taxes" => array("tax1" => $invoice->client->tax, "tax2" => $invoice->client->tax2)
        );
    }

    public function createInvoiceJSON()
    {
        global $whmcs;
        $data       = Request::get("invoice");
        $publish    = Request::get("publish");
        $reseller   = Reseller::getLogged();

        try
        {
            //Validate if userid belongs to reseller
            if(!$reseller->clients->find($data["userid"])->exists)
            {
                throw new \Exception(Lang::T("create", "error", "userid"));
            }

            $invoiceDates = InvoiceDateHelper::generateDefaultInvoiceDates();

            $data["date"] = $data["date"] ?: $invoiceDates['date'];
            $data["duedate"] = $data["duedate"] ?: $invoiceDates['duedate'];

            if(!isset($data['paymentmethod'])) throw new \Exception(Lang::T('create','error','paymentMethodMissing'));

            $isPaymentGatewayConfigured = PaymentGatewayModel::where('gateway',$data['paymentmethod'])
                ->where('setting','enabled')
                ->where('value','on')
                ->where('reseller_id',$reseller->id)
                ->get()->first();

            if(!$isPaymentGatewayConfigured) throw new \Exception(Lang::T('create','error','paymentMethodNotConfigured'));

            //Set Unpaid status if the publish param is set to 1 or 2 (publish or publish and email)
            $status = $publish > 0 ?  RCInvoicesRepo::STATUS_UNPAID : RCInvoicesRepo::STATUS_DRAFT;

            $invoice = new Invoice();
            $invoice->create($reseller->id, $data["invoicenum"], $data["userid"], $data["date"], $data["duedate"], $status, $data["paymentmethod"], $data["tax1"], $data["tax2"]);

            foreach($data["items"] as $raw)
            {
                $taxed = $raw["taxed"] ?: 0;

                $item = new Item();
                $item->create($reseller->id, $invoice->id, $invoice->userid, "", "", $raw["description"], $raw["amount"], $taxed, $invoice->duedate, $invoice->paymentmethod);
            }

            //Update created invoice
            $invoice->updateInvoiceTotals();

            //Send email if reseller selected publish & send email
            if($publish > 1)
            {
                $invoice->sendMessage("Invoice Created");
            }

            //Push invoice number
            $reseller->settings->getNextInvoiceNumber();
        }
        catch(\Exception $ex)
        {
            return ["error" => $ex->getMessage()];
        }

        return ["success" => Lang::T("create", "success")];
    }
    
    /**
     * Edit invoice items
     * 
     * @since 3.0.0
     * @return type
     */
    public function updateInvoiceJSON()
    {
        $data = Request::get("invoice");
        $invoice = new Invoice($data["invoiceid"]);
        $invoice->update($data);
        
        return array("success" => Lang::T('update','success'));
    }
    
    /**
     * Update invoice status
     * This function is only for RC invoices!
     * 
     * @since 3.1.0
     * @return type
     */
    public function updateInvoiceStatusJSON()
    {
        $invoiceid = Request::get("invoiceid");
        $status = Request::get("status");

        try {
            $invoice = new Invoice($invoiceid);
            $invoice->updateStatus($status);

            if ($status != WhmcsInvoices::STATUS_UNPAID) {
                $creditLineService = new CreditLineService();
                $creditLineService->addPayment($invoice->getModel());
            }

        } catch(\Exception $ex) {
            return array("error" => Lang::T($ex->getMessage()));
        }
        
        return array("success" => Lang::T('update','success'));
    }

    /**
     * Publish draft invoice
     *
     * @return array
     */
    public function publishJSON()
    {
        $send = Request::get("send");
        $invoiceid = Request::get("invoiceid");

        try
        {
            $invoice = new Invoice($invoiceid);
            $invoice->updateStatus(RCInvoicesRepo::STATUS_UNPAID);

            if($send)
            {
                $invoice->sendMessage("Invoice Created");
            }
        }
        catch(\Exception $ex)
        {
            return array("error" => Lang::T($ex->getMessage()));
        }

        return array("success" => Lang::T('update','success'));
    }
    
    /**
     * Add Transaction to RC Invoice
     * 
     * @since 3.1.0
     * @return type
     */
    public function addTransactionJSON()
    {
        $invoiceid = Request::get("invoiceid");
        $payment = Request::get("payment");
        
        try 
        {
            $invoice = new Invoice($invoiceid);
            $invoice->payments->addTransaction(0, $payment["transid"], $payment["amount"], $payment["fees"], $payment["gateway"], $payment["date"]);

            $creditLineService = new CreditLineService();
            $creditLineService->addPayment($invoice->getModel());
        } 
        catch(\Exception $ex) 
        {
            return array("error" => Lang::T($ex->getMessage()));
        }
        
        return array("success" => Lang::T('addTransaction','success'));
    }
    
    /**
     * Delete Transaction from RC Invoice
     * 
     * @since 3.1.0
     * @return type
     */
    public function deleteTransactionJSON()
    {
        $transactionid = Request::get("transactionid");
        
        $repo = new TransactionsRepo();
        $repo->delete($transactionid);
        
        return array("success" => Lang::T('deleteTransaction','success'));
    }
    
    /**
     * Get all WHMCS invoices
     * 
     * @since 3.0.0
     * @return type
     */
    public function getWHMCSInvoicesForTableJSON()
    {
        //Used in client view
        $clientid = Request::get("clientid");
        
        $dtRequest = Request::getDatatableRequest();
        $reseller = Reseller::getLogged();
        
        $invoices = new WhmcsInvoices();
        $result = $invoices->getInvoicesForTable($reseller->id, $dtRequest, $clientid);

        $format = [ "status" => ["lang" => ["table", "paymentstatus"]]];
        
        $buttons =
            [[
                "type" => "only-icon",
                "class" => "openDetailsInvoice btn-primary",
                "data" => ["invoiceid" => "id"],
                "icon" => "fa fa-list-ul",
                "tooltip" => Lang::T('table','detailsInfo')]];
        
        $datatable = new Datatable($format, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }
    
    /**
     * Get all RC invoices
     * 
     * @since 3.1.0
     * @return type
     */
    public function getRCInvoicesForTableJSON()
    {
        //Used in client view
        $clientid = Request::get("clientid");
        
        $dtRequest = Request::getDatatableRequest();
        $reseller = Reseller::getLogged();
        
        $invoices = new RCInvoicesRepo();
        $result = $invoices->getInvoicesForTable($reseller->id, $dtRequest, $clientid);
        
        $buttons = array(
            array(
                "type" => "only-icon", 
                "class" => "openEditInvoice btn-primary", 
                "data" => array("invoiceid" => "id"), 
                "icon" => "fa fa-list-ul",
                "tooltip" => Lang::T('table','detailsInfo')
            ),
        );

        $format = [ "status" => ["lang" => ["table", "paymentstatus"]]];
        
        $datatable = new Datatable($format, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }
    
    public function getInvoiceTransactionsForTableJSON()
    {
        $reseller = Reseller::getLogged();
        
        $invoiceid = Request::get("invoiceid");
        $dtRequest = Request::getDatatableRequest();

        $repo = new TransactionsRepo();
        $result = $repo->getForTable($dtRequest, $invoiceid);
        
        $invoice = new Invoice($invoiceid);
        $currency = $invoice->client->currencyObj;
        
        foreach($result["data"] as $row)
        {
            //Set Payment Method Names
            $gatewaysRepo = new PaymentGateways();
            $settings = $gatewaysRepo->getGatewaySettings($reseller->id, $row->gateway);
            $row->gateway = $settings["displayName"];
            
            //Add currency prefix and suffix
            $amount = $row->amount ?: 0;
            $row->amount = formatCurrency($amount, $currency->id);
            $row->fees = formatCurrency($row->fees, $currency->id);
        }
       
        $buttons = array(
            array(
                "type" => "only-icon", 
                "class" => "deleteTransaction btn-danger", 
                "data" => array("transactionid" => "id"), 
                "icon" => "fa fa-trash",
                "tooltip" => Lang::T('edit','transactions', 'table', 'deleteInfo')
            ),
        );
        
        $datatable = new Datatable(null, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }

    public function processDownloadPdfHTML()
    {
        $reseller = Reseller::getLogged();
        $invoiceId = Request::get("invoiceid");
        $data = "";

        if ( $reseller->exists && $reseller->settings->admin->resellerInvoice) {
            $data = $this->generateRCInvoicePdf($invoiceId);
        } else {
            $data = $this->generateWHMCSInvoicePdf($invoiceId);
        }

        header('Content-type:application/pdf');
        header("Content-Transfer-Encoding: binary");
        echo $data;
        exit();
    }

    protected function generateRCInvoicePdf($invoiceId)
    {
        $invoice = new Invoice($invoiceId);
        $invoice->invoicenum = $invoice->invoicenum != "" ? $invoice->invoicenum : $invoice->id;

        $pdf = new Pdf($invoice);

        $invoiceName = Lang::T("rcinvoicefilename"). '-' . $invoice->invoicenum. ".pdf";

        ob_clean();
        header('Content-Disposition: inline; filename="' . $invoiceName. '"');

        return $pdf->getFile();
    }

    protected function generateWHMCSInvoicePdf($invoiceId)
    {
        global$whmcs;
        $whmcsInvoice = new WhmcsInvoiceExtended($invoiceId);
        $whmcsInvoice->pdfCreate();

        if (!function_exists("pdfInvoicePage")) {
            require_once Loader::$whmcsDir."/includes/invoicefunctions.php";
        }

        $whmcsInvoice->pdfInvoicePage($invoiceId);

        $num = $whmcsInvoice->getData()['invoicenum'];
        $invoiceName = $whmcs->get_lang("invoicefilename") . $num . ".pdf";

        $whmcsInvoice->setTitle($invoiceName);

        ob_clean();
        header('Content-Disposition: inline; filename="' . $invoiceName . '"');

        return $whmcsInvoice->pdfOutput();
    }
}