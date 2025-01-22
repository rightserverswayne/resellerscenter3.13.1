<?php

namespace MGModule\ResellersCenter\repository;

use MGModule\ResellersCenter\libs\GlobalSearch\SearchTypes;
use MGModule\ResellersCenter\models\Transaction;
use MGModule\ResellersCenter\repository\source\AbstractRepository;

use MGModule\ResellersCenter\models\whmcs\Invoice as WHMCSInvoice;
use MGModule\ResellersCenter\core\Session;
use \Illuminate\Database\Capsule\Manager as DB;
use MGModule\ResellersCenter\Repository\Source\InvoiceRepoInterface;
use MGModule\ResellersCenter\models\Invoice;
use MGModule\ResellersCenter\models\InvoiceItem;
use MGModule\ResellersCenter\repository\whmcs\Invoices as WhmcsInvoicesRepo;

/**
 * Description of Invoices
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Invoices extends AbstractRepository implements InvoiceRepoInterface
{
    const STATUS_PAID = "Paid";
    const STATUS_UNPAID = "Unpaid";
    const STATUS_CANCELLED = "Cancelled";
    const STATUS_DRAFT = "Draft";

    const STATUSES = array("Paid", "Unpaid", "Cancelled");
    
    public function determinateModel()
    {
        return Invoice::class;
    }

    public function getInvoiceItemsRepo()
    {
        return new InvoiceItems();
    }
        
    public function createFromWHMCSInvoice($resellerid, WHMCSInvoice $invoice)
    {
        global $CONFIG;
        $duedate = $invoice->duedate;
        
        if($CONFIG["OrderDaysGrace"] && Session::get("cart"))
        {
            $duedate = date("Y-m-d", strtotime("{$duedate} +{$CONFIG["OrderDaysGrace"]} days"));
        }
        
        $data = array(
            "reseller_id"   => $resellerid,
            "userid"        => $invoice->userid,
            "invoicenum"    => $invoice->invoicenum,
            "date"          => $invoice->date,
            "duedate"       => $duedate,
            "datepaid"      => $invoice->datepaid,
            "last_capture_attempt" => $invoice->last_capture_attempt,
            "subtotal"      => $invoice->subtotal,
            "credit"        => 0, //Applied credits have to be added through Invoice::addCreditPayment()
            "tax"           => $invoice->tax,
            "tax2"          => $invoice->tax2,
            "total"         => $invoice->total,
            "taxrate"       => $invoice->taxrate,
            "taxrate2"      => $invoice->taxrate2,
            "status"        => Invoices::STATUS_UNPAID,
            "paymentmethod" => $invoice->paymentmethod,
            "notes"         => $invoice->notes
        );

        return $this->create($data);
    }

    public function getByClient($clientid)
    {
        $model  = $this->getModel();
        $result = $model->where("userid", $clientid)->get();

        return $result;
    }

    public function getByClientAndStatus($clientid, $status = null)
    {
        $model = $this->getModel();
        
        if($status !== null) 
        {
            $result = $model->where("userid", $clientid)->where("status", $status)->get();
        }
        else
        {
            $result = $model->where("userid", $clientid)->get();
        }
        
        return $result;
    }

    public function getEndClientInvoicesByClient($clientid)
    {
        return $this->getEndClientInvoicesByClientQuery($clientid)->get();
    }

    public function getEndClientInvoicesIdsByClient($clientid)
    {
        return $this->getEndClientInvoicesByClient($clientid)->pluck('id')->toArray();
    }

    public function getByWHMCSInvoiceId($id)
    {
        $model = $this->getModel();
        return $model->where('relinvoice_id', $id)->first();
    }

    public function getMergeInvoice($userid)
    {
        $result = $this->getModel()
                        ->select("ResellersCenter_Invoices.*")
                        ->where("ResellersCenter_Invoices.userid", $userid)
                        ->whereNotNull("ResellersCenter_InvoiceItems.id")
                        ->leftJoin("ResellersCenter_InvoiceItems", function($join)
                        {
                            $join->on("ResellersCenter_InvoiceItems.invoice_id", "=", "ResellersCenter_Invoices.id");
                            $join->where("ResellersCenter_InvoiceItems.type", "=", InvoiceItems::TYPE_INVOICE);
                        })
                        ->orderBy("id", "desc")
                        ->first();

        return $result;
    }
    
    public function getByTime($startDate, $endDate, $resellerid = null)
    {
        $model = $this->getModel();
        
        if($resellerid == null)
        {
            $invoices = $model->where("date", ">=", $startDate)->where("date", "<", $endDate)->get();
        }
        else
        {
            $invoices = $model->where("date", ">=", $startDate)->where("date", "<", $endDate)->where("reseller_id", $resellerid)->get();
        }
        
        return $invoices;
    }

    public function getInvoicesForTable($resellerid, $dtRequest, $clientid = null)
    {
        $query = DB::table("ResellersCenter_Invoices");
        $query->leftJoin("tblclients", "tblclients.id", "=", "ResellersCenter_Invoices.userid");
        $query->leftJoin("tblcurrencies", "tblcurrencies.id", "=", "tblclients.currency");
        $query->leftjoin("ResellersCenter_PaymentGateways", function ($join) use ($resellerid)
        {
            $join->on("ResellersCenter_PaymentGateways.gateway", "=", "ResellersCenter_Invoices.paymentmethod");
            $join->where("ResellersCenter_PaymentGateways.setting", "=", "displayName");
            $join->where("ResellersCenter_PaymentGateways.reseller_id", "=", DB::raw("{$resellerid}"));
        });
        $query->leftjoin("tblpaymentgateways", function($join)
        {
            $join->on("tblpaymentgateways.gateway", "=", "ResellersCenter_Invoices.paymentmethod");
            $join->where("tblpaymentgateways.setting", "=", "name");
        });

        $query->where("ResellersCenter_Invoices.reseller_id", $resellerid);

        if($clientid != null) {
            $query->where("userid", $clientid);
        }

        $totalCount = $query->count();

        $filter = $dtRequest->filter;
        if(!empty($filter))
        {
            $query->where(function($query) use ($filter)
            {
                $filter = DB::getPdo()->quote("%{$filter}%");
                $query->orWhere(DB::raw("DATE_FORMAT(ResellersCenter_Invoices.date, '%Y-%m-%d')"), "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhere(DB::raw("DATE_FORMAT(ResellersCenter_Invoices.duedate, '%Y-%m-%d')"), "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhere("paymentmethod", "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhere("tblpaymentgateways.value", "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhere("tblclients.id",            "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhere("tblclients.firstname",     "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhere("tblclients.lastname",      "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhere("total",      "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhereRaw(DB::raw("CASE ResellersCenter_Invoices.invoicenum WHEN '' THEN ResellersCenter_Invoices.id LIKE CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci ELSE ResellersCenter_Invoices.invoicenum LIKE CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci END"));
            });
        }

        $query->select(
            DB::raw("DISTINCT ResellersCenter_Invoices.id, ResellersCenter_Invoices.date, ResellersCenter_Invoices.duedate, ResellersCenter_Invoices.status"),
            DB::raw("IFNULL(ResellersCenter_PaymentGateways.value, IFNULL(tblpaymentgateways.value, ResellersCenter_Invoices.paymentmethod)) as paymentmethod"),
            DB::raw("IF(ResellersCenter_Invoices.invoicenum = '', ResellersCenter_Invoices.id, ResellersCenter_Invoices.invoicenum) as invoicenum"),
            DB::raw("CONCAT('#', tblclients.id, ' ', tblclients.firstname, ' ', tblclients.lastname) as client"),
            DB::raw("tblclients.id as userid"),
            DB::raw("CONCAT(tblcurrencies.prefix, ResellersCenter_Invoices.total + ResellersCenter_Invoices.credit, tblcurrencies.suffix) as total"),
            DB::raw("ResellersCenter_Invoices.total + ResellersCenter_Invoices.credit as totalsort")
        );

        $displayAmount = $query->count();
        $query->take($dtRequest->limit)->skip($dtRequest->offset);

        $orderCol = $dtRequest->columns[$dtRequest->orderBy] == "total" ? "totalsort" : $dtRequest->columns[$dtRequest->orderBy];
        $orderCol = ($orderCol == "invoicenum") ? DB::raw("LENGTH(invoicenum), invoicenum") : $orderCol;
        $query->orderBy($orderCol, $dtRequest->orderDir);

        $data = $query->get();

        return array("data" => $data, "displayAmount" => $displayAmount,"totalAmount" => $totalCount);
    }

    public function getDaysBeforeDue($days)
    {
        $model = $this->getModel();
        $invoices = $model->where("duedate", "=", date("Y-m-d", strtotime("+{$days} Days")))
                            ->where("status", self::STATUS_UNPAID)
                            ->get();

        return $invoices;
    }

    public function getDaysAfterDue($days)
    {
        $model = $this->getModel();
        $invoices = $model->where("duedate", "=", date("Y-m-d", strtotime("-{$days} Days")))
                            ->where("status", self::STATUS_UNPAID)
                            ->get();

        return $invoices;
    }

    public function getEndClientInvoicesByClientQuery($clientid)
    {
        $model = $this->getModel();

        $invoicesTbl = (new Invoice())->getTable();
        $itemsTbl = (new InvoiceItem())->getTable();

        return $model->select($invoicesTbl.".*")->where($invoicesTbl.".userid", $clientid)
            ->where($invoicesTbl.".status", '!=' , self::STATUS_DRAFT)
            ->where(function($query) use ($itemsTbl)
            {
                $query->where($itemsTbl.".type", '!=' , InvoiceItems::TYPE_INVOICE);
                $query->orWhereNull($itemsTbl.".id");
            })
            ->leftJoin($itemsTbl, $itemsTbl.".invoice_id", "=", $invoicesTbl.".id")
            ->groupBy( $invoicesTbl.".id");
    }

    public function getEndClientInvoicesBalance($clientid)
    {
        $model = $this->getModel();
        $invoicesTbl = (new Invoice())->getTable();
        $transactionsTbl = (new Transaction())->getTable();

        return $model->select(DB::raw("total-IFNULL((SELECT SUM(amountin-amountout) FROM ".$transactionsTbl." WHERE ".$transactionsTbl.".invoice_id=".$invoicesTbl.".id),0) AS balance"))
            ->where($invoicesTbl.".userid", $clientid)
            ->where($invoicesTbl.".status", WhmcsInvoicesRepo::STATUS_UNPAID)
            ->get()
            ->sum('balance');
    }

    public function getEndClientInvoicesOverdueBalance($clientid)
    {
        $model = $this->getModel();
        $invoicesTbl = (new Invoice())->getTable();
        $transactionsTbl = (new Transaction())->getTable();

        return $model->select(DB::raw("total-IFNULL((SELECT SUM(amountin-amountout) FROM ".$transactionsTbl." WHERE ".$transactionsTbl.".invoice_id=".$invoicesTbl.".id),0) AS balance"))
            ->where($invoicesTbl.".userid", $clientid)
            ->where($invoicesTbl.".status", WhmcsInvoicesRepo::STATUS_UNPAID)
            ->where($invoicesTbl.'.duedate', '<', date('Y-m-d'))
            ->get()
            ->sum('balance');
    }

    public function getEndClientInvoicesOverdueCount($clientid)
    {
        $model = $this->getModel();
        $invoicesTbl = (new Invoice())->getTable();

        return $model->where($invoicesTbl.".userid", $clientid)
            ->where($invoicesTbl.".status", WhmcsInvoicesRepo::STATUS_UNPAID)
            ->where($invoicesTbl.'.duedate', '<', date('Y-m-d'))
            ->get()
            ->count();
    }

    public function getInvoicesSubTotalByInvoicesIds($invoicesIds)
    {
        $model = $this->getModel();
        return $model
            ->whereIn('id', $invoicesIds)
            ->get()
            ->sum('subtotal');
    }

    public function getInvoicesTotalByInvoicesIds($invoicesIds)
    {
        $model = $this->getModel();
        return $model
            ->whereIn('id', $invoicesIds)
            ->get()
            ->sum('total');
    }

    public function getSummaryCreditByInvoiceIds($invoicesIds)
    {
        $model = $this->getModel();
        return $model
            ->whereIn('id', $invoicesIds)
            ->get()
            ->sum('credit');
    }

    public function getInvoicesTotalTaxByInvoicesIds($invoicesIds)
    {
        $model = $this->getModel();
        return $model
            ->whereIn('id', $invoicesIds)
            ->get()
            ->sum('tax');
    }

    public function getInvoicesTotalTax2ByInvoicesIds($invoicesIds)
    {
        $model = $this->getModel();
        return $model
            ->whereIn('id', $invoicesIds)
            ->get()
            ->sum('tax2');
    }

    public function getByRelationInvoiceId($whmcsInvoiceId)
    {
        $model = $this->getModel();
        return $model
            ->where('relinvoice_id', $whmcsInvoiceId)
            ->first();
    }

    public function getNextId()
    {
        $model = $this->getModel();
        $result = $model->select('id')->orderBy('id', 'desc')->first();
        $lastId = $result->id ?: 0;
        return ++$lastId;
    }

    public function getInvoicesForGlobalSearch($resellerId, $filter)
    {
        $model = $this->getModel();
        $invoiceTable = $model->getTable();

        $query = DB::table($invoiceTable)
            ->select($invoiceTable.'.id')
            ->addSelect(DB::raw('"'.SearchTypes::INVOICE_TYPE.'" AS type'))
            ->addSelect(DB::raw("IF(".$invoiceTable.".invoicenum = '', ".$invoiceTable.".id, invoicenum) as name"))
            ->addSelect(DB::raw($invoiceTable. ".status"))
            ->addSelect(DB::raw($invoiceTable. ".date"))
            ->addSelect(DB::raw($invoiceTable. ".userid as client_id"))
            ->where('reseller_id', $resellerId);

        $query->where(function($query) use($filter, $invoiceTable)
        {
            $query->orWhere($invoiceTable.".id", "LIKE", "%$filter%")
                ->orWhere($invoiceTable.".invoicenum", "LIKE", "%$filter%")
                ->orWhere($invoiceTable.".date", "LIKE", "%$filter%")
                ->orWhere($invoiceTable.".total", "LIKE", "%$filter%");
        });

        return $query;
    }

    public function getUnpaidInvoicesCount($resellerId)
    {
        $model = $this->getModel();
        return $model->where('reseller_id', $resellerId)->where('status', self::STATUS_UNPAID)->count();
    }

}
