<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use MGModule\ResellersCenter\models\ResellerService;
use MGModule\ResellersCenter\models\whmcs\Invoice;
use MGModule\ResellersCenter\models\whmcs\Transaction;
use MGModule\ResellersCenter\repository\Invoices;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;
use \Illuminate\Database\Capsule\Manager as DB;

use MGModule\ResellersCenter\repository\Resellers;

/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Transactions extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Transaction';
    }
    
    public function getTransactionsByReseller($resellerid, $start = null, $end = null)
    {
        $invoices = $this->getResellerInvoicesIds($resellerid);
        if(empty($invoices)) {
            return array();
        }
        
        $query = DB::table("tblaccounts");
        $query->whereIn("tblaccounts.invoiceid", $invoices);
        
        if(!empty($start)) {
            $query->where("date", ">", $start);
        }
        
        if(!empty($end)) {
            $query->where("date", "<", $end);
        }

        return $query->get();
    }
    
    public function getTransactionsForTable($resellerid, $dtRequest)
    {
        $currencies = new Currencies();
        $defaultCurrency = $currencies->getDefault();
        
        $dtCols = array("id", "client", "date", "gateway", "description", "amountin", "fees", "amountout", "actions");
        $invoices = $this->getResellerInvoicesIds($resellerid);
        
        if(empty($invoices)) {
            return array("data" => array(), "displayAmount" => 0, "totalAmount" => 0);
        }
        
        $query = DB::table("tblaccounts");
        $query->join("tblclients", "tblclients.id", "=", "tblaccounts.userid")
              ->leftJoin("tblcurrencies", "tblcurrencies.id", "=", DB::raw("IF(tblaccounts.currency = 0, {$defaultCurrency->id}, tblaccounts.currency)"));

        $query->whereIn("tblaccounts.invoiceid", $invoices);
        $totalCount = $query->count();
        
        $filter = $dtRequest->filter;
        if(!empty($filter))
        {
            $query->where(function($query) use ($filter){
                $query->orWhere("tblaccounts.id",           "LIKE", "%$filter%")
                      ->orWhere("tblaccounts.date",         "LIKE", "%$filter%")
                      ->orWhere("tblaccounts.description",  "LIKE", "%$filter%")
                      ->orWhere("tblaccounts.gateway",      "LIKE", "%$filter%")
                      ->orWhere("tblaccounts.amountin",      "LIKE", "%$filter%")
                      ->orWhere("tblclients.id",            "LIKE", "%$filter%")
                      ->orWhere("tblclients.firstname",     "LIKE", "%$filter%")
                      ->orWhere("tblclients.lastname",      "LIKE", "%$filter%");
            });
        }
        $displayAmount = $query->count();
        $query->select("tblaccounts.*")
                ->addSelect(DB::raw("CONCAT('#', tblclients.id, ' ', tblclients.firstname, ' ', tblclients.lastname) as client"))
                ->addSelect(DB::raw("CONCAT(tblcurrencies.prefix, tblaccounts.amountin, tblcurrencies.suffix) as amountin"));

        if(!empty($dtRequest))
        {
            $query->take($dtRequest->limit)->skip($dtRequest->offset);
            $query->orderBy($dtCols[$dtRequest->orderBy], $dtRequest->orderDir);
        }
        
        $data = $query->get();
        return array("data" => $data, "displayAmount" => $displayAmount, "totalAmount" => $totalCount);
    }

    public static function getTransactionsArrayByInvoiceId($invoiceId): array
    {
        $transactionsDB = Transaction::select('tblaccounts.id', 'tblaccounts.date', 'tblpaymentgateways.value','amountin', 'tblcurrencies.id as currencyid', 'code', 'prefix', 'suffix', 'format', 'tblcurrencies.rate')
            ->leftJoin("tblpaymentgateways", "tblpaymentgateways.gateway", "=", "tblaccounts.gateway")
            ->leftJoin("tblinvoices", "tblinvoices.id", "=", 'tblaccounts.invoiceid')
            ->leftJoin("tblclients", "tblclients.id", "=", 'tblinvoices.userid')
            ->leftJoin("tblcurrencies", "tblcurrencies.id", "=", 'tblclients.currency')
            ->where('tblaccounts.invoiceid',$invoiceId )->where('tblpaymentgateways.setting','name' )->get();

        $transactions = [];
        foreach ($transactionsDB as $transactionDB) {
            $transaction['id'] = $transactionDB->id;
            $transaction['date'] = $transactionDB->date;
            $transaction['gateway'] = $transactionDB->value;
            $currency = ['id'=>$transactionDB->currencyid,
                'code'=>$transactionDB->code,
                'suffix'=>$transactionDB->suffix,
                'prefix'=>$transactionDB->prefix,
                'format'=>$transactionDB->format,
                'rate'=>$transactionDB->rate ];
            $price = new \WHMCS\View\Formatter\Price($transactionDB->amountin, $currency);
            $price->format();
            $transaction['amount'] = $price;
            $transactions[] = $transaction;
        }
        return $transactions;
    }
    
    private function getResellerInvoicesIds($resellerid)
    {
        $hostingTypes = [InvoiceItems::TYPE_HOSTING, InvoiceItems::TYPE_ABHOSTING, InvoiceItems::TYPE_ABHOSTING_ITEM];
        return ResellerService::select('tblinvoiceitems.invoiceid as id')
              ->join('tblinvoiceitems', static function( $query ) use ($hostingTypes) {
                  //Services
                  $query->where(static function( $query ) use ($hostingTypes) {
                      $query->on('tblinvoiceitems.relid', '=', 'ResellersCenter_ResellersServices.relid');
                      $query->on('ResellersCenter_ResellersServices.type', '=', DB::raw("'hosting'"));
                      $query->whereIn('tblinvoiceitems.type', $hostingTypes);
                  });
                  //Services addon
                  $query->orWhere(static function( $query ) {
                      $query->on('tblinvoiceitems.relid', '=', 'ResellersCenter_ResellersServices.relid');
                      $query->on('ResellersCenter_ResellersServices.type', "=", DB::raw("'addon'"));
                      $query->whereIn('tblinvoiceitems.type', [InvoiceItems::TYPE_ADDON]);
                  });
                  //Domains
                  $query->orWhere(static function( $query ) {
                      $query->on('tblinvoiceitems.relid', '=', 'ResellersCenter_ResellersServices.relid');
                      $query->on('ResellersCenter_ResellersServices.type', '=', DB::raw("'domain'"));
                      $query->whereIn('tblinvoiceitems.type', [InvoiceItems::TYPE_DOMAIN_RENEW, InvoiceItems::TYPE_DOMAIN_TRANSFER, InvoiceItems::TYPE_DOMAIN_REGISTER]);
                  });
              })
            ->where('reseller_id',$resellerid)
              ->get()
              ->pluck('id')
              ->toArray();
    }

    public function getOverdueBalanceByInvoicesIds($invoicesIds)
    {
        $invoiceTable = (new Invoice())->getTable();
        $model = $this->getModel();
        return $model->select(DB::raw('(total - SUM(amountin - amountout)) as balance'))
            ->leftJoin($invoiceTable, $invoiceTable.'.id', '=' ,'invoiceid')
            ->whereIn('invoiceid', $invoicesIds)
            ->where($invoiceTable.'.duedate', '<', date('Y-m-d'))
            ->where($invoiceTable.'.status', Invoices::STATUS_UNPAID)
            ->get()
            ->sum('balance');
    }

    public function getTransactionsBalanceByInvoiceIds($invoicesIds)
    {
        $model = $this->getModel();
        return $model->select(DB::raw('(SUM(amountin - amountout)) as balance'))
            ->whereIn('invoiceid', $invoicesIds)
            ->get()
            ->sum('balance');
    }
}
