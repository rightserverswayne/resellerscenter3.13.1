<?php
namespace MGModule\ResellersCenter\Core\Resources\Transactions;

use MGModule\ResellersCenter\Core\Resources\ResourceObject;
use MGModule\ResellersCenter\models\whmcs\Currency;
use MGModule\ResellersCenter\repository\whmcs\Currencies;

/**
 * Description of Transaction.php
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Transaction extends ResourceObject
{
    protected function getModelClass()
    {
        return \MGModule\ResellersCenter\Models\Transaction::class;
    }

    public function create($transid, $description, $userid, $gateway, $currency, $date, $amountin, $fees, $invoiceid, $refundid)
    {
        $repo = new Currencies();
        $currency = $currency ? $repo->find($currency) : $repo->getDefault();

        $classname = $this->getModelClass();
        $this->model = new $classname();

        $this->model->create([
            "transid"       => $transid,
            "description"   => $description,
            "userid"        => $userid,
            "gateway"       => $gateway,
            "currency"      => $currency->id,
            "rate"          => $currency->rate,
            "date"          => $date,
            "amountin"      => $amountin,
            "fees"          => $fees,
            "invoice_id"    => $invoiceid,
            "refundid"      => $refundid
        ]);
    }
}