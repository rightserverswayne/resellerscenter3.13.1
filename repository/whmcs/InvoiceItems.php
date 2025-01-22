<?php
namespace MGModule\ResellersCenter\repository\whmcs;

use MGModule\ResellersCenter\repository\source\AbstractRepository;
use MGModule\ResellersCenter\models\whmcs\InvoiceItem;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class InvoiceItems extends AbstractRepository
{
    const TYPE_HOSTING  = 'Hosting';
    const TYPE_ADDON    = 'Addon';
    const TYPE_UPGRADE  = 'Upgrade';
    const TYPE_SETUP    = 'Setup';
    const TYPE_ABHOSTING    = 'ABHostingItem';
    const TYPE_ABHOSTING_ITEM    = 'ABHostingItem';

    const TYPE_DOMAIN_REGISTER  = 'DomainRegister';
    const TYPE_DOMAIN_TRANSFER  = 'DomainTransfer';
    const TYPE_DOMAIN_RENEW     = 'Domain';

    const TYPE_INVOICE  = 'Invoice';
    const TYPE_RC_ORDER = 'RCOrder';
    
    const TYPE_GROUP_DISCOUNT = 'GroupDiscount';
    const TYPE_PROMO_HOSTING = 'PromoHosting';
    const TYPE_PROMO_DOMAIN = 'PromoDomain';

    const TYPE_COMPLETED_PREFIX = 'Rc';
    const TYPE_COMPLETED_SUFFIX = 'Completed';

    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\InvoiceItem';
    }
    
    public function getItemByRelidAndType($relid, $type)
    {
        $model = $this->getModel();
        $item = $model->where("relid", "=", $relid)->where("type", "=", $type)->first();
        
        return $item;
    }
    
    public function getItemsByInvoiceId($invoiceid)
    {
        $items = new InvoiceItem();
        $result = $items->where("invoiceid", $invoiceid)->get();

        return $result;
    }

    public function getItemsByInvoiceIdAndUserID($invoiceid,$userid)
    {
        $items = new InvoiceItem();
        $result = $items->where("invoiceid", $invoiceid)->where('userid',$userid)->get();

        return $result;
    }

    public function getByInvoiceAndClient($invoiceid, $userid)
    {
        $model = $this->getModel();
        $result = $model->where("invoiceid", $invoiceid)->where("userid", $userid)->get();

        return $result;
    }
    
    public function getByInvoiceAndRelidAndType($invoiceid, $relid, $type)
    {
        $model = $this->getModel();
        $result = $model->where("invoiceid", $invoiceid)->where("relid", $relid)->where("type", $type)->first();
        
        return $result;
    }
    
    public function getByInvoiceAndItemId($invoiceid, $itemId)
    {
        $model = $this->getModel();
        $result = $model->where("invoiceid", $invoiceid)->where("id", '>', $itemId)->get();
        
        return $result;
    }
    
    public function getMaxItemId()
    {
        $items = new InvoiceItem();
        $result = $items->where("invoiceid", '!=', 0)->max('id');

        return $result;
    }

    public function deleteNotAssignedByClientId($userid)
    {
        $model = $this->getModel();
        $model->where("invoiceid", 0)->where("userid", $userid)->delete();
    }

    public function getLastNotAssignedByClientId($userid)
    {
        $model = $this->getModel();
        return $model->where("invoiceid", 0)
            ->where("userid", $userid)
            ->orderBy('id', 'DESC')
            ->first();
    }
}
