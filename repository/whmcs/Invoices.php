<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use MGModule\ResellersCenter\libs\GlobalSearch\SearchTypes;
use MGModule\ResellersCenter\models\whmcs\Addon;
use MGModule\ResellersCenter\models\whmcs\Domain;
use MGModule\ResellersCenter\models\whmcs\Hosting;
use MGModule\ResellersCenter\models\whmcs\Invoice;
use MGModule\ResellersCenter\models\whmcs\InvoiceItem;
use MGModule\ResellersCenter\models\whmcs\Transaction;
use MGModule\ResellersCenter\models\whmcs\Upgrade;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;
use \Illuminate\Database\Capsule\Manager as DB;
use MGModule\ResellersCenter\Repository\Source\InvoiceRepoInterface;
use MGModule\ResellersCenter\repository\whmcs\Invoices as WhmcsInvoices;

/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Invoices extends AbstractRepository implements InvoiceRepoInterface
{
    const STATUS_PAID = 'Paid';
    const STATUS_UNPAID = 'Unpaid';
    const STATUS_CANCELLED = 'Cancelled';
    const STATUS_REFUNDED = 'Refunded';
    const STATUS_DRAFT = 'Draft';

    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Invoice';
    }

    public function getInvoiceItemsRepo()
    {
        return new InvoiceItems();
    }
    
    public function createAttachment($invoiceid)
    {
        global $whmcs;
        global $attachments_dir;
        
        $model = $this->getModel();
        $invoice = $model->find($invoiceid);
        
        $invoice->invoicenum = $invoice->branded->invoicenum ? $invoice->branded->invoicenum : $invoice->id;
   
        //Generate filename
        $filename = uniqid() . "_" . $whmcs->get_lang("invoicefilename") . $invoice->invoicenum . ".pdf";
        $fullpath = ($attachments_dir ?: \MGModule\ResellersCenter\Addon::getWHMCSDIR(). "attachments");
        if (substr(trim($fullpath), -1) == DS) {
            $fullpath = substr(trim($fullpath), 0, -1);
        }
        $fullpath.= DS.$filename;

        $whmcsInvoice = new \WHMCS\Invoice($invoice->id);
        $whmcsInvoice->pdfCreate();
        $whmcsInvoice->pdfInvoicePage($invoice->id);
        $data = $whmcsInvoice->pdfOutput();
        
        file_put_contents($fullpath, $data);
        
        return $filename;
    }

    public function getByClient($clientid)
    {
        $model = $this->getModel();
        return $model->where("userid", $clientid)->get();
    }

    public function getInvoicesForTable( $resellerid, $dtRequest, $clientid = null ):array
    {
        $invoicesIds = $this->getAvailableInvoices($resellerid);

        $query = DB::table('ResellersCenter_ResellersClients')
                   ->select('tblinvoices.*')
                   ->addSelect(DB::raw('IFNULL(IF(tblinvoices.status = "'.Invoices::STATUS_DRAFT.'", tblinvoices.invoicenum, ResellersCenter_BrandedInvoices.invoicenum),tblinvoices.invoicenum) AS invoicenum'))
                   ->addSelect(DB::raw("CONCAT('#', tblclients.id, ' ', tblclients.firstname,' ', tblclients.lastname) AS client"))
                   ->addSelect(DB::raw("CONCAT(tblcurrencies.prefix, tblinvoices.total + tblinvoices.credit,tblcurrencies.suffix) AS total"))
                   ->addSelect(DB::raw("IFNULL(tblpaymentgateways.value, tblinvoices.paymentmethod) AS paymentmethod"))
                   ->addSelect(DB::raw("tblinvoices.total + tblinvoices.credit AS totalsort"))
                   ->join('tblclients', 'ResellersCenter_ResellersClients.client_id', '=', 'tblclients.id')
                   ->join('tblcurrencies', 'tblcurrencies.id', '=', 'tblclients.currency')
                   ->join('tblinvoices', 'tblclients.id', '=', 'tblinvoices.userid')
                   ->leftJoin('ResellersCenter_BrandedInvoices', 'tblinvoices.id', '=', 'ResellersCenter_BrandedInvoices.invoice_id')
                   ->leftjoin("tblpaymentgateways", function( $join ) {
                       $join->on("tblpaymentgateways.gateway", "=", "tblinvoices.paymentmethod");
                       $join->where("tblpaymentgateways.setting", "=", "name");
                   })
                   ->where('ResellersCenter_ResellersClients.reseller_id', '=', $resellerid)
                   ->whereIn('tblinvoices.id', $invoicesIds);

        if( !empty($clientid) ) {
            $query->where("tblinvoices.userid", $clientid);
        }

        $query->groupBy("tblinvoices.id");
        $totalCount = count($query->get());

        $filter = $dtRequest->filter;
        if ( !empty($filter) ) {
            $query->where(function( $query ) use ( $filter ) {
                $filter = DB::getPdo()->quote("%{$filter}%");
                $query->orWhere("tblinvoices.id", "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhere(DB::raw("DATE_FORMAT(tblinvoices.date, '%Y-%m-%d')"), "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhere(DB::raw("DATE_FORMAT(tblinvoices.duedate, '%Y-%m-%d')"), "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhere("tblinvoices.paymentmethod", "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhere("tblpaymentgateways.value", "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhere("tblclients.id", "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhere("tblclients.firstname", "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhere("tblclients.lastname", "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhere("total", "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"))
                      ->orWhere("ResellersCenter_BrandedInvoices.invoicenum", "LIKE", DB::raw("CAST($filter AS CHAR CHARACTER SET utf8) COLLATE utf8_unicode_ci"));
            });
        }

        $displayAmount = count($query->get());
        $query->take($dtRequest->limit)->skip($dtRequest->offset);

        $orderCol = $dtRequest->columns[$dtRequest->orderBy] === "total" ? "totalsort" : $dtRequest->columns[$dtRequest->orderBy];
        $query->orderBy($orderCol, $dtRequest->orderDir);
        $data = $query->get();

        return ["data" => $data, "displayAmount" => $displayAmount, "totalAmount" => $totalCount];
    }

    public function getResellerRelatedClientInvoicesIds($clientId):array
    {
        $result = $this->getResellerRelatedClientInvoicesQuery($clientId)->get();
        return $result->pluck('id')->toArray();
    }

    public function getResellerNoRelatedClientInvoicesIds($clientId):array
    {
        $result = $this->getResellerNoRelatedClientInvoicesQuery($clientId)->get();
        return $result->pluck('id')->toArray();
    }

    public function getInvoicesCountersFromQuery($query, $table = 'tblinvoices'):array
    {
        $countersTypes = [Invoices::STATUS_PAID, Invoices::STATUS_UNPAID, Invoices::STATUS_CANCELLED, Invoices::STATUS_REFUNDED];
        $counters = [];

        foreach ($countersTypes as $countersType) {
            $queryTemp = clone $query;
            $counters[$countersType] = $queryTemp->where($table.'.status', $countersType)->get()->count();
        }

        return $counters;
    }

    public function getResellerNoRelatedClientInvoicesQuery($clientId)
    {
        $query = $this->getClientsInvoicesQuery($clientId);

        return $query->where(function ($where) {
            $where->whereNull("tblhosting.id");
            $where->whereNull("tblhostingaddons.id");
            $where->whereNull("tbldomains.id");
            $where->whereNull("tblupgrades.id");
        })->groupBy('tblinvoices.id');
    }

    public function getResellerRelatedClientInvoicesQuery($clientId)
    {
        $query = $this->getClientsInvoicesQuery($clientId);

        return $query->where(function ($where) {
            $where->whereNotNull("tblhosting.id");
            $where->orWhereNotNull("tblhostingaddons.id");
            $where->orWhereNotNull("tbldomains.id");
            $where->orWhereNotNull("tblupgrades.id");
        })->groupBy('tblinvoices.id');
    }

    public function getBalanceByInvoicesIds($invoicesIds)
    {
        $model = $this->getModel();

        return $model->select(DB::raw("total-IFNULL((SELECT SUM(amountin-amountout) FROM tblaccounts WHERE tblaccounts.invoiceid=tblinvoices.id),0) AS balance"))
            ->whereIn('id', $invoicesIds)
            ->where('tblinvoices.status', self::STATUS_UNPAID)
            ->get()
            ->sum('balance');
    }

    public function getOverdueBalanceByInvoicesIds($invoicesIds)
    {
        $model = $this->getModel();

        return $model->select(DB::raw("total-IFNULL((SELECT SUM(amountin-amountout) FROM tblaccounts WHERE tblaccounts.invoiceid=tblinvoices.id),0) AS balance"))
            ->whereIn('id', $invoicesIds)
            ->where('tblinvoices.status', self::STATUS_UNPAID)
            ->where('tblinvoices.duedate', '<', date('Y-m-d'))
            ->get()
            ->sum('balance');
    }

    public function getUnpaidInvoicesOverdueCountFromQuery($query)
    {
        return $query->where('tblinvoices.status',Invoices::STATUS_UNPAID)->where('tblinvoices.duedate', '<', date('Y-m-d'))->get()->count();
    }

    public function getSummaryCreditByInvoiceIds($invoicesIds)
    {
        $model = $this->getModel();
        return $model
            ->whereIn('id', $invoicesIds)
            ->get()
            ->sum('credit');
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

    public function getParentInvoiceIdFromAvailableIds($invoicesIds)
    {
        $model = $this->getModel();
        $invoiceTable = $model->getTable();
        $invoiceItemsTable = (new InvoiceItem())->getTable();

        return $model->select($invoiceTable.'.id')
            ->leftJoin($invoiceItemsTable, $invoiceItemsTable.'.invoiceid', '=', $invoiceTable.'.id')
            ->whereIn($invoiceItemsTable.'.relid', $invoicesIds)
            ->where($invoiceTable.'.status', self::STATUS_UNPAID)
            ->where($invoiceItemsTable.'.type', InvoiceItems::TYPE_INVOICE)
            ->first()->id;
    }

    public function activeLastCancelledNorelatedInvoice($clientId)
    {
        $this->marksUnpaidFirstInvoiceByInvoiceIds($this->getResellerNoRelatedClientInvoicesIds($clientId));
    }

    public function activeLastCancelledRelatedInvoice($clientId)
    {
        $this->marksUnpaidFirstInvoiceByInvoiceIds($this->getResellerRelatedClientInvoicesIds($clientId));
    }

    public function isParentInvoice($invoiceId)
    {
        $model = $this->getModel();
        $invoiceTable = $model->getTable();
        $invoiceItemsTable = (new InvoiceItem())->getTable();

        $result = $model
            ->leftJoin($invoiceItemsTable, $invoiceItemsTable.'.invoiceid', '=', $invoiceTable.'.id')
            ->where($invoiceItemsTable.'.invoiceid', $invoiceId)
            ->where($invoiceItemsTable.'.type', InvoiceItems::TYPE_INVOICE)
            ->first();

        return $result->exists;
    }

    public function isResellerRelated($invoiceId):bool
    {
        $model = $this->getModel();
        $invoice = $model->find($invoiceId);
        $relatedInvoiceIds = $this->getResellerRelatedClientInvoicesIds($invoice->userid);

        foreach ($invoice->items as $item) {
            if (in_array($item->relid, $relatedInvoiceIds)) {
                return true;
            }
        }

        return false;
    }

    public function getNextId()
    {
        $model = $this->getModel();
        $result = $model->select('id')->orderBy('id', 'desc')->first();
        $lastId = $result->id ?: 0;
        return ++$lastId;
    }

    public function getAvailableInvoices($resellerid):array
    {
        $query = DB::table('ResellersCenter_ResellersClients')
            ->select('tblinvoices.id')
            ->join('tblclients', 'ResellersCenter_ResellersClients.client_id', '=', 'tblclients.id')
            ->join('tblinvoices', 'tblclients.id', '=', 'tblinvoices.userid')
            ->join('tblinvoiceitems', 'tblinvoiceitems.invoiceid', '=', 'tblinvoices.id')
            ->leftJoin('tblhosting', static function( $join ) use ( $resellerid ) {
                $join->on('tblhosting.id', '=', 'tblinvoiceitems.relid')
                    ->whereRaw("tblhosting.id IN (SELECT `ResellersCenter_ResellersServices`.`relid` 
                    FROM `ResellersCenter_ResellersServices` 
                    WHERE `ResellersCenter_ResellersServices`.`type` = 'hosting'  
                    AND `ResellersCenter_ResellersServices`.`reseller_id` = " . $resellerid . ')');
            })
            ->leftJoin('tblhostingaddons', static function( $join ) use ( $resellerid ) {
                $join->on('tblhostingaddons.id', '=', 'tblinvoiceitems.relid')
                    ->whereRaw("tblhostingaddons.id IN (SELECT `ResellersCenter_ResellersServices`.`relid` 
                    FROM `ResellersCenter_ResellersServices`
                    WHERE `ResellersCenter_ResellersServices`.`type` = 'addon'  
                    AND `ResellersCenter_ResellersServices`.`reseller_id` = " . $resellerid . ')');
            })
            ->leftJoin('tbldomains', static function( $join ) use ( $resellerid ) {
                $join->on('tbldomains.id', '=', 'tblinvoiceitems.relid')
                    ->whereRaw("tbldomains.id IN (SELECT `ResellersCenter_ResellersServices`.`relid` 
                    FROM `ResellersCenter_ResellersServices` 
                    WHERE `ResellersCenter_ResellersServices`.`type` = 'domain'  
                    AND `ResellersCenter_ResellersServices`.`reseller_id` = " . $resellerid . ')');
            })
            ->leftJoin('tblupgrades', static function( $join ) use ( $resellerid ) {
                $join->on('tblupgrades.id', '=', 'tblinvoiceitems.relid')
                    ->whereRaw("tblupgrades.relid IN (SELECT `ResellersCenter_ResellersServices`.`relid` 
                    FROM `ResellersCenter_ResellersServices` 
                    WHERE `ResellersCenter_ResellersServices`.`type` = 'hosting'  
                    AND `tblupgrades`.`type` = 'package' 
                    AND `ResellersCenter_ResellersServices`.`reseller_id` = " . $resellerid . ' )');
            })
            ->where(function( $where ) {
                $where->whereNotNull("tblhosting.id");
                $where->orWhereNotNull("tblhostingaddons.id");
                $where->orWhereNotNull("tbldomains.id");
                $where->orWhereNotNull("tblupgrades.id");
            })->get();

        return $query->pluck('id')->toArray();
    }

    protected function getClientsInvoicesQuery($clientId)
    {
        return DB::table('ResellersCenter_ResellersClients')
            ->select('tblinvoices.*')
            ->join('tblinvoices', 'ResellersCenter_ResellersClients.client_id', '=', 'tblinvoices.userid')
            ->join('tblinvoiceitems', 'tblinvoiceitems.invoiceid', '=', 'tblinvoices.id')
            ->leftJoin('tblhosting', static function ($join) {
                $join->on('tblhosting.id', '=', 'tblinvoiceitems.relid')
                    ->whereRaw("tblhosting.id IN (SELECT `ResellersCenter_ResellersServices`.`relid` FROM `ResellersCenter_ResellersServices` WHERE `ResellersCenter_ResellersServices`.`type` = 'hosting'  AND `ResellersCenter_ResellersServices`.`reseller_id` = `ResellersCenter_ResellersClients`.`reseller_id`)" );
            })
            ->leftJoin('tblhostingaddons', static function ($join)  {
                $join->on('tblhostingaddons.id', '=', 'tblinvoiceitems.relid')
                    ->whereRaw("tblhostingaddons.id IN (SELECT `ResellersCenter_ResellersServices`.`relid` FROM `ResellersCenter_ResellersServices` WHERE `ResellersCenter_ResellersServices`.`type` = 'addon'  AND `ResellersCenter_ResellersServices`.`reseller_id` = `ResellersCenter_ResellersClients`.`reseller_id`)");
            })
            ->leftJoin('tbldomains', static function ($join) {
                $join->on('tbldomains.id', '=', 'tblinvoiceitems.relid')
                    ->whereRaw("tbldomains.id IN (SELECT `ResellersCenter_ResellersServices`.`relid` FROM `ResellersCenter_ResellersServices` WHERE `ResellersCenter_ResellersServices`.`type` = 'domain'  AND `ResellersCenter_ResellersServices`.`reseller_id` = `ResellersCenter_ResellersClients`.`reseller_id`)");
            })
            ->leftJoin('tblupgrades', static function ($join) {
                $join->on('tblupgrades.id', '=', 'tblinvoiceitems.relid')
                    ->whereRaw("tblupgrades.relid IN (SELECT `ResellersCenter_ResellersServices`.`relid` FROM `ResellersCenter_ResellersServices` WHERE `ResellersCenter_ResellersServices`.`type` = 'hosting'  AND `ResellersCenter_ResellersServices`.`reseller_id` = `ResellersCenter_ResellersClients`.`reseller_id`)");
            })->where('ResellersCenter_ResellersClients.client_id', $clientId)
            ->where('tblinvoiceitems.type', '!=', InvoiceItems::TYPE_INVOICE);
    }

    protected function marksUnpaidFirstInvoiceByInvoiceIds($invoicesIds)
    {
        $model = $this->getModel();
        $invoiceTable = $model->getTable();
        $invoiceItemsTable = (new InvoiceItem())->getTable();

        $result = Invoice::select($invoiceTable.'.id')
            ->leftJoin($invoiceItemsTable, $invoiceItemsTable.'.invoiceid', '=', $invoiceTable.'.id')
            ->whereIn($invoiceItemsTable.'.relid', $invoicesIds)
            ->where($invoiceTable.'.status', self::STATUS_CANCELLED)
            ->where($invoiceItemsTable.'.type', InvoiceItems::TYPE_INVOICE)
            ->groupBy($invoiceTable.'.id')
            ->latest($invoiceTable.'.id')
            ->first();

        if ($result->exists) {
            Invoice::find($result->id)->update(['status'=>self::STATUS_UNPAID]);
        }
    }

    public function getInvoicesForGlobalSearch($resellerId, $filter)
    {
        $model = $this->getModel();
        $invoiceTable = $model->getTable();
        $invoicesIds = $this->getAvailableInvoices($resellerId);

        $query = DB::table($invoiceTable)
            ->select($invoiceTable.'.id')
            ->addSelect(DB::raw('"'.SearchTypes::INVOICE_TYPE.'" AS type'))
            ->addSelect(DB::raw("IF(".$invoiceTable.".invoicenum = '', ".$invoiceTable.".id, invoicenum) as name"))
            ->addSelect(DB::raw($invoiceTable. ".status"))
            ->addSelect(DB::raw($invoiceTable. ".date"))
            ->addSelect(DB::raw($invoiceTable. ".userid as client_id"))
            ->whereIn('tblinvoices.id', $invoicesIds);

        $query->where(function($query) use($filter, $invoiceTable)
        {
            $query->orWhere($invoiceTable.".id", "LIKE", "%$filter%")
                ->orWhere($invoiceTable.".invoicenum", "LIKE", "%$filter%")
                ->orWhere($invoiceTable.".date", "LIKE", "%$filter%")
                ->orWhere($invoiceTable.".total", "LIKE", "%$filter%");
        });

        return $query;
    }

    public function getUnpaidRelatedInvoicesCount($resellerId)
    {
        $invoicesIds = $this->getAvailableInvoices($resellerId);

        $model = $this->getModel();

        return $model
            ->select('tblinvoices.*')
            ->where('status', self::STATUS_UNPAID)
            ->whereIn('tblinvoices.id', $invoicesIds)
            ->count();
    }

    public function getUserUnpaidInvoicesCount($userId)
    {
        $model = $this->getModel();
        return $model->where('userid', $userId)->where('status', self::STATUS_UNPAID)->count();
    }
}

