<?php

namespace MGModule\ResellersCenter\repository\whmcs;

use MGModule\ResellersCenter\libs\GlobalSearch\SearchTypes;
use MGModule\ResellersCenter\models\ResellerService;
use MGModule\ResellersCenter\models\whmcs\InvoiceItem;
use MGModule\ResellersCenter\models\whmcs\Upgrade;
use MGModule\ResellersCenter\repository\ResellersServices;
use MGModule\ResellersCenter\repository\source\AbstractRepository;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Collection as CollectionModel;

/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Orders extends AbstractRepository
{
    const STATUS_ACTIVE = "Active";
    const STATUS_PENDING = "Pending";
    const STATUS_FRAUD = "Fraud";
    const STATUS_CANCELLED = "Cancelled";

    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Order';
    }
    
    public function getRelated($resellerid, $clientid = null)
    {               
        $query = DB::table("tblorders");
        $query->leftJoin("tblinvoices", "tblinvoices.id", "=", "tblorders.invoiceid");
        $query->leftJoin("tblinvoiceitems", "tblinvoiceitems.invoiceid", "=", "tblinvoices.id");
        $query->leftJoin("tblclients", "tblclients.id", "=", "tblinvoices.userid");
        
        //Realtion - Invoice must be realted with one reseller service
        $query->join("ResellersCenter_ResellersServices", function($join) use ($resellerid) {
            $join->on("ResellersCenter_ResellersServices.reseller_id", "=", DB::raw("'$resellerid'"));
            $join->on("ResellersCenter_ResellersServices.relid", "=", "tblinvoiceitems.relid");
            $join->where(function($where) {
                $where->orWhere("ResellersCenter_ResellersServices.type", "=", 'hosting');
                $where->orWhere("ResellersCenter_ResellersServices.type", "=", 'addon');
                $where->orWhere("ResellersCenter_ResellersServices.type", "=", 'domain');
            });
        });
        
        $query->whereNotNull("ResellersCenter_ResellersServices.id");
        if($clientid) {
            $query->where("tblorders.userid", "=", $clientid);
        }
        
        $query->groupBy("tblorders.id");
        $query->select("tblorders.*");
        return $query->get();
    }

    public function getResellerOrdersForTable($resellerid, $dtRequest, $clientid = null)
    {
        $hostingOrderIds = $this->getResellerOrderIdsByType($resellerid, ResellersServices::TYPE_HOSTING);
        $addonOrderIds = $this->getResellerOrderIdsByType($resellerid, ResellersServices::TYPE_ADDON);
        $domainOrderIds = $this->getResellerOrderIdsByType($resellerid, ResellersServices::TYPE_DOMAIN);
        $upgradeOrderIds = $this->getResellerUpgradeOrderIds($resellerid);

        $mergedOrderIds = array_merge($hostingOrderIds, $addonOrderIds, $domainOrderIds, $upgradeOrderIds);

        $query = DB::table("tblorders");
        $query->leftJoin("tblinvoices", "tblinvoices.id", "=", "tblorders.invoiceid");
        $query->leftJoin("tblclients", "tblclients.id", "=", "tblorders.userid");

        $query->join("ResellersCenter_ResellersClients", function($join) use ($resellerid){
            $join->on("ResellersCenter_ResellersClients.reseller_id","=", DB::raw("'$resellerid'"));
            $join->on("ResellersCenter_ResellersClients.client_id","=","tblorders.userid");
        });

        $query->leftJoin("ResellersCenter_Invoices", "ResellersCenter_Invoices.relinvoice_id", "=", "tblinvoices.id");

        $query->leftJoin("tblcurrencies", "tblcurrencies.id", "=", "tblclients.currency");

        $query->whereIn("tblorders.id", $mergedOrderIds );//->orWhereNotNull("tblinvoiceitems.id");

        $query->select("tblorders.id")
                ->addSelect("tblorders.ordernum")
                ->addSelect("tblorders.date")
                ->addSelect(DB::raw("CONCAT(tblclients.firstname, ' ', tblclients.lastname) as client"))
                ->addSelect(DB::raw("IF(ISNULL(ResellersCenter_Invoices.paymentmethod), tblorders.paymentmethod, ResellersCenter_Invoices.paymentmethod) as paymentmethod"))
                ->addSelect("tblinvoices.status as paymentstatus")
                ->addSelect("tblorders.invoiceid")
                ->addSelect("tblorders.status")
                ->addSelect(DB::raw("CONCAT(tblcurrencies.prefix, tblorders.amount, ' ', tblcurrencies.suffix) as amount"));

        if ($clientid) {
            $query->where("tblorders.userid", "=", $clientid);
        }

        $query->groupBy("tblorders.id");
        $totalAmount = count($query->get());

        //Add filters
        if (!empty($dtRequest->filter)) {
            $filter = $dtRequest->filter;
            $query->where(function($query) use ($filter){
                $query->orWhere("tblorders.id",             "LIKE", "%$filter%")
                      ->orWhere("tblorders.ordernum",       "LIKE", "%$filter%")
                      ->orWhere("tblorders.date",           "LIKE", "%$filter%")
                      ->orWhere("tblorders.paymentmethod",  "LIKE", "%$filter%")
                      ->orWhere("tblorders.status",         "LIKE", "%$filter%")
                      ->orWhere("tblorders.amount",         "LIKE", "%$filter%")
                      ->orWhere("tblinvoices.status",       "LIKE", "%$filter%")
                      ->orWhere("tblclients.firstname",     "LIKE", "%$filter%")
                      ->orWhere("tblclients.lastname",      "LIKE", "%$filter%")
                      ->orWhere("ResellersCenter_Invoices.paymentmethod",  "LIKE", "%$filter%");
            });
        }

        $displayAmount = count($query->get());

        $query->take($dtRequest->limit)->skip($dtRequest->offset);
        $query->orderBy($dtRequest->columns[$dtRequest->orderBy], $dtRequest->orderDir);

        $result = $query->get();

        //final changes to data
        foreach ($result as $key => $data) {
            $model = $this->getModel();
            $order = $model->find($data->id);

            //Get order status from Invoice (if invoice exists)
            if (!empty($order->invoice->resellerInvoice)) {
                $result[$key]->paymentstatus = $order->invoice->resellerInvoice->status;
            }

            //Look for reseller awaiting invoice
            $invoice = $order->getResellerInvoice();

            //Add row class
            $result[$key]->DT_RowClass = $invoice->status == 'Unpaid' ? 'awaiting-reseller' : '';
        }

        $orderDir = $dtRequest->orderDir;
        if ($dtRequest->columns[$dtRequest->orderBy] === 'paymentstatus') {
            uasort($result->toArray(), static function($a,$b) use($orderDir)
            {
                if ( $orderDir === 'asc' ) {
                    return (strtolower($a->paymentstatus) > strtolower($b->paymentstatus)) ? 1 : -1;
                } else {
                    return (strtolower($a->paymentstatus) > strtolower($b->paymentstatus)) ? -1 : 1;
                }
            });
        }

        return ["data" => $result, "displayAmount" => $displayAmount, "totalAmount" => $totalAmount];
    }

    protected function getResellerOrderIdsByType($resellerId, $type):array
    {
        $model = $this->getModel();

        $tblOrders = (new $model())->getTable();
        $tblRelated = ResellersServices::getTableByType($type);
        $tblResServ = (new ResellerService())->getTable();

        $result = $model::select($tblOrders.'.id')
            ->leftJoin($tblRelated, $tblRelated.".orderid", "=", $tblOrders.".id")
            ->leftJoin($tblResServ, $tblRelated.".id", "=", $tblResServ.".relid")
            ->where($tblResServ.".type", $type)
            ->where($tblResServ.".reseller_id", $resellerId)
            ->groupBy($tblOrders.".id")
            ->get();

        return $this->parseIdsFromCollection($result);

    }

    protected function getResellerUpgradeOrderIds($resellerId):array
    {
        $model = $this->getModel();

        $tblOrders = (new $model())->getTable();
        $tblUpgrades = (new Upgrade())->getTable();
        $tblResServ = (new ResellerService())->getTable();

        $result = $model::select($tblOrders.'.id')
            ->leftJoin($tblUpgrades, $tblUpgrades.".orderid", "=", $tblOrders.".id")
            ->leftJoin($tblResServ, $tblUpgrades.".relid", "=", $tblResServ.".relid")
            ->where($tblResServ.".type", ResellersServices::TYPE_HOSTING)
            ->where($tblResServ.".reseller_id", $resellerId)
            ->groupBy($tblOrders.".id")
            ->get();

        return $this->parseIdsFromCollection($result);

    }

    protected function parseIdsFromCollection(CollectionModel $result):array
    {
        $ids = [];
        foreach ($result as $rawId) {
            $ids[] = $rawId->id;
        }
        return $ids;
    }

    public function getResellerOrdersForGlobalSearch($resellerId, $filter)
    {
        $hostingOrderIds = $this->getResellerOrderIdsByType($resellerId, ResellersServices::TYPE_HOSTING);
        $addonOrderIds = $this->getResellerOrderIdsByType($resellerId, ResellersServices::TYPE_ADDON);
        $domainOrderIds = $this->getResellerOrderIdsByType($resellerId, ResellersServices::TYPE_DOMAIN);
        $upgradeOrderIds = $this->getResellerUpgradeOrderIds($resellerId);

        $mergedOrderIds = array_merge($hostingOrderIds, $addonOrderIds, $domainOrderIds, $upgradeOrderIds);

        $query = DB::table("tblorders");

        $query->whereIn("tblorders.id", $mergedOrderIds );

        $query->where(function($query) use($filter)
        {
            $query->orWhere("tblorders.ordernum", "LIKE", "%$filter%")
                ->orWhere("tblorders.date", "LIKE", "%$filter%")
                ->orWhere("tblorders.amount", "LIKE", "%$filter%")
                ->orWhere("tblorders.id", "LIKE", "%$filter%");
        });

        $query->select("tblorders.id")
            ->addSelect(DB::raw('"'.SearchTypes::ORDER_TYPE.'" AS type'))
            ->addSelect("tblorders.ordernum as name")
            ->addSelect("tblorders.status")
            ->addSelect("tblorders.date")
            ->addSelect("tblorders.userid as client_id");

        $query->groupBy("tblorders.id");

        return $query;
    }
}