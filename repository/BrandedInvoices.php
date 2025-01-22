<?php
namespace MGModule\ResellersCenter\repository;
use MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of Invoices
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class BrandedInvoices extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\BrandedInvoice';
    }
   
    public function createNew($resellerid, $invoiceid, $invoicenum)
    {
        $model = $this->getModel();
        $model->reseller_id = $resellerid;
        $model->invoice_id = $invoiceid;
        $model->invoicenum = $invoicenum;
        
        $model->save();
        return $model->id;
    }
}
