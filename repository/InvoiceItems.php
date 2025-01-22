<?php
namespace MGModule\ResellersCenter\repository;
use MGModule\ResellersCenter\repository\source\AbstractRepository;


/**
 * Description of InvoiceItems
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class InvoiceItems extends AbstractRepository
{    
    const TYPE_HOSTING  = 'Hosting';
    const TYPE_ADDON    = 'Addon';
    const TYPE_UPGRADE  = 'Upgrade';
    const TYPE_ABHOSTING    = 'ABHosting';
    const TYPE_ABHOSTING_ITEM    = 'ABHostingItem';

    const TYPE_DOMAIN_REGISTER  = 'DomainRegister';
    const TYPE_DOMAIN_TRANSFER  = 'DomainTransfer';
    const TYPE_DOMAIN_RENEW     = 'Domain';
    
    const TYPE_INVOICE          = 'Invoice';
    const TYPE_GROUP_DISCOUNT   = 'GroupDiscount';
    const TYPE_PROMO_HOSTING    = 'PromoHosting';
    const TYPE_PROMO_DOMAIN     = 'PromoDomain';
    
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\InvoiceItem';
    }
    
    public function createFromWHMCSInvoiceItem($resellerid, $invoiceid, \MGModule\ResellersCenter\models\whmcs\InvoiceItem $item)
    {
        $data = array(
            "reseller_id"   => $resellerid,
            "invoice_id"    => $invoiceid,
            "userid"        => $item->userid,
            "type"          => $item->type,
            "relid"         => $item->relid,
            "description"   => $item->description,
            "amount"        => $item->amount,
            "taxed"         => $item->taxed, 
            "duedate"       => $item->duedate,
            "paymentmethod" => $item->paymentmethod,
            "notes"         => $item->notes
        );
        
        return $this->create($data);
    }

    public function getByTypeAndRelid($type, $relid)
    {
        $model = $this->getModel();
        $result = $model->where("type", $type)->where("relid", $relid)->get();
        
        return $result;
    }
}
