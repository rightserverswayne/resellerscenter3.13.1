<?php
namespace MGModule\ResellersCenter\Core\Resources\Invoices;
use MGModule\ResellersCenter\core\helpers\ClientAreaHelper;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;

/**
 * Description of Pdf
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Pdf
{
    /**
     * @var
     */
    protected $file;

    /**
     * @var Invoice
     */
    protected $invoice;

    /**
     * Create PDF file for Invoice
     *
     * @param Invoice $invoice
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;

        $this->create();
        $this->addInvoicePage();
    }

    /**
     * Get PDF data
     *
     * @return mixed
     */
    public function getFile()
    {
        return $this->file->Output("", "S");
    }

    /**
     * Save created PDF
     *
     * @param string $filename
     * @param string $dir
     * @return string
     */

    public function saveFile($filename = "", $dir = "")
    {
        global $whmcs;
        $invoicenum = $this->invoice->invoicenum ?? $this->invoice->id;

        //Generate filename
        $filename = empty($filename) ? uniqid() . "_" . $whmcs->get_lang("invoicefilename") . $invoicenum : $filename;
        $filename = preg_replace("/[^a-zA-Z0-9-_ ]/", "", $filename);

        $fullpath = ($dir ?: $whmcs->getAttachmentsDir()) . DS."{$filename}.pdf";
        $data = $this->file->Output("", "S");

        file_put_contents($fullpath, $data);

        return "{$filename}.pdf";
    }

    /**
     * Create PDF
     *
     * @return \TCPDF
     */
    protected function create()
    {
        global $whmcs;

        $langArray = array();
        $langArray['a_meta_charset'] = $whmcs->get_config("Charset");
        $langArray['a_meta_dir'] = "ltr";
        $langArray['a_meta_language'] = "en";
        $langArray['w_page'] = "page";
        
        $unicode = (strtolower(substr($whmcs->get_config("Charset"), 0, 3)) == "iso" ? false : true);
        
        $this->file = new \TCPDF("P", "mm", "A4", $unicode, $whmcs->get_config("Charset"), false);
        $this->file->SetCreator("WHMCS V" . $whmcs->get_config("Version"));
        $this->file->SetAuthor($whmcs->get_config("CompanyName"));
        $this->file->SetMargins(15, 25, 15);
        $this->file->SetFooterMargin(15);
        $this->file->SetAutoPageBreak(TRUE, 25);
        $this->file->setLanguageArray($langArray);
        $this->file->setPrintHeader(false);
        $this->file->setPrintFooter(false);
        
        return $this->file;
    }

    /**
     * Create Invoice Page
     */
    protected function addInvoicePage()
    {
        global $whmcs;

        $this->file->SetTitle($whmcs->get_lang("invoicenumber") . $this->invoice->invoicenum);
        $this->file->AddPage();
        $this->file->SetFont($whmcs->get_config("TCPDFFont"), "", 10);
        $this->file->SetTextColor(0);

        //Set variables to local scope
        foreach($this->getVariables() as $k => $v)
        {
            ${$k} = $v;
        }

        $templateName = $whmcs->getClientAreaTemplate()->getName();

        if($this->invoice->reseller_id)
        {
            $reseller = new Reseller($this->invoice->reseller_id);
            $templateName = $reseller->settings->private->whmcsTemplate ?: $templateName;
        }

        $pdf = &$this->file;
        include ROOTDIR . "/templates/" . $templateName . "/invoicepdf.tpl";
    }

    /**
     * @return array
     */
    protected function getVariables()
    {
        global $whmcs;

        $params =
        [
            "rcinvoice"      => true,
            "invoiceid"      => $this->invoice->id,
            "invoicenum"     => $this->invoice->invoicenum ?: $this->invoice->id,

            "paymentmethod"  => $this->invoice->paymentmethod,
            "paymentmodule"  => $this->invoice->paymentmethod,
            "gateway"        => $this->invoice->paymentmethod, //TODO: set gateway dispaly name
            "balance"        => $this->invoice->total - $this->invoice->amountpaid,

            "date"          => $this->invoice->date,
            "duedate"       => $this->invoice->duedate,
            "datepaid"      => (substr($this->invoice->datepaid, 0, 10) == "0000-00-00") ? "" : $this->invoice->datepaid,
            "datecreated"   => $this->invoice->date,

            "subtotal"      => formatCurrency($this->invoice->subtotal, $this->invoice->client->currency),
            "credit"        => formatCurrency($this->invoice->credit,   $this->invoice->client->currency),
            "tax"           => formatCurrency($this->invoice->tax,      $this->invoice->client->currency),
            "tax2"          => formatCurrency($this->invoice->tax2,     $this->invoice->client->currency),
            "total"         => formatCurrency($this->invoice->total,    $this->invoice->client->currency),
            "balance"       => formatCurrency($this->invoice->total - $this->invoice->amountpaid, $this->invoice->client->currency),
            "amountpaid"    => formatCurrency($this->invoice->amountpaid, $this->invoice->client->currency),

            "userid"        => $this->invoice->client->id,
            "clientsdetails"=> $this->invoice->client->toArray(),
            "country"       => $this->invoice->client->country,

            "taxrate"       => $this->invoice->taxrate ?: "",
            "taxname"       => $this->invoice->client->tax->name != "" && $this->invoice->tax != 0 ? $this->invoice->client->tax->name : "",
            "taxrate2"      => $this->invoice->taxrate2 ?: "",
            "taxname2"      => $this->invoice->client->tax2->name != "" && $this->invoice->tax2 != 0 ? $this->invoice->client->tax2->name : "",

            "status"        => $this->invoice->status,
            "pagetitle"     => $whmcs->get_lang("invoicenumber") . $this->invoice->invoicenum,
            "payto"         => nl2br($this->invoice->reseller->settings->private->payto),
            "notes"         => nl2br($this->invoice->notes),

            "companyname"    => $this->invoice->reseller->settings->private->companyname,
            "companyurl"     => $this->invoice->reseller->settings->private->domain,
            "companyaddress" => explode("\n", $this->invoice->reseller->settings->private->payto),
            "customLogo"     => "../../" . ClientAreaHelper::getLogoPath() . $this->invoice->reseller->settings->private->logo,

            "invoiceitems"   => $this->parseLineItems($this->invoice->items),
            "transactions"   => $this->parseTransactions($this->invoice->transactions),

            //      TODO:
            //            "clienttotaldue"
            //            "clientpreviousbalance"
            //            "clientbalancedue"
            //            "lastpaymentamount"
            //            "lastpaymenttransid"
        ];
        
        $clientCustomFields = $this->getClientCustomFields();
        $params["clientsdetails"] = array_merge($params["clientsdetails"], $clientCustomFields);

        foreach($this->invoice->client->customFields->getAvailable() as $field)
        {
            $value = $field->getValueByRelid($this->invoice->client->id);
            if($field->showinvoice === 'on' && $value)
            {
                $name = $field->fieldname;
                if(strpos($field->fieldname, '|') !== false)
                {
                    $name = explode('|',$field->fieldname)[1];
                }
                
                $params["customfields"][] = [
                    'fieldname' => $name,
                    'value'     => $value
                ];
            }
        }
        
        $countries = new \WHMCS\Utility\Country();
        $params['clientsdetails']['country'] = $countries->getName($params['clientsdetails']['country']);
        
        return $params;
    }

    /**
     * @param $items
     * @return array
     */
    protected function parseLineItems($items)
    {
        global $whmcs;

        $result = array();
        foreach($items as $item)
        {
            $result[] = array(
                "id"            => $item->id,
                "type"          => $item->type,
                "relid"         => $item->relid,
                "description"   => ($item->qty > 1) ? html_entity_decode("{$item->qty} x {$item->description} @ {$item->amount} {$whmcs->get_lang('invoiceqtyeach')}") : html_entity_decode($item->description),
                "rawamount"     => $item->amount,
                "amount"        => formatCurrency($item->amount, $item->invoice->client->currency)
            );
        }

        return $result;
    }

    protected function parseTransactions($transactions)
    {
        $result = array();
        foreach($transactions as $index => $tran)
        {
            $result[$index] = $tran->toArray();
            $result[$index]["amount"] = formatCurrency($tran->amount, $tran->currency);
        }

        return $result;
    }

    /**
     * @param $value
     * @param $type
     *
     * @return string
     */
    private function formatValueByType( $value, $type )
    {
        switch( $type )
        {
            case 'tickbox':
                return $value === 'on' ? 'Yes' : 'No';
            default:
                return $value;
        }
    }
    
    private function getClientCustomFields()
    {
        $result = array();
        foreach ($this->invoice->client->customfields as $key => $field)
        {
            $value  = $field->getValueByRelid($this->invoice->client->id);
            $i      = $key+1;
            $result["customfields"][] = array(
                "id"    => $field->id,
                "value" => $value,
            );
            $result["customfields{$i}"] = $value;
        }
        
        return $result;
    }
}
