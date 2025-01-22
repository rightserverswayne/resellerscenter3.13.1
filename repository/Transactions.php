<?php
namespace MGModule\ResellersCenter\repository;
use MGModule\ResellersCenter\repository\source\AbstractRepository;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of InvoiceItems
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Transactions extends AbstractRepository
{    
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\Transaction';
    }
    
    public function createNew($data)
    {
        $model = $this->getModel();
        return $model->fill($data)->save();
    }
    
    public function getByTransId($transid)
    {
        $model = $this->getModel();
        $transaction = $model->where("transid", $transid)->first();
        
        return $transaction;
    }
    
    public function getByClientId($id)
    {
        $model = $this->getModel();
        return $model->where('userid', $id)->get();
    }

    public function getForTable($dtRequest, $invoiceid = null, $resellerid = null)
    {
        $currencies = new whmcs\Currencies();
        $defaultCurrency = $currencies->getDefault();
        
        $query = DB::table("ResellersCenter_Transactions");
        $query->leftJoin("tblclients", "tblclients.id", "=", "ResellersCenter_Transactions.userid");
        $query->leftJoin("tblcurrencies", "tblcurrencies.id", "=", DB::raw("IF(ResellersCenter_Transactions.currency = 0, {$defaultCurrency->id}, ResellersCenter_Transactions.currency)"));
        
        if($resellerid != null) 
        {
            $query->join("ResellersCenter_Invoices", function($join) use ($resellerid)
            {
                $join->on("ResellersCenter_Transactions.invoice_id", "=", "ResellersCenter_Invoices.id");   
                $join->on("ResellersCenter_Invoices.reseller_id", "=", DB::raw("$resellerid"));   
            });
        }
        
        if($invoiceid != null) {
            $query->where("invoice_id", $invoiceid);
        }

        $totalCount = $query->count();

        $filter = $dtRequest->filter;
        if(!empty($filter))
        {
            $query->where(function($query) use ($filter){
                $query->orWhere("ResellersCenter_Transactions.id",      "LIKE", "%$filter%")
                      ->orWhere("ResellersCenter_Transactions.date",    "LIKE", "%$filter%")
                      ->orWhere("ResellersCenter_Transactions.gateway", "LIKE", "%$filter%")
                      ->orWhere("ResellersCenter_Transactions.transid", "LIKE", "%$filter%")
                      ->orWhere("ResellersCenter_Transactions.fees",    "LIKE", "%$filter%")
                      ->orWhere("ResellersCenter_Transactions.description",    "LIKE", "%$filter%")
                      ->orWhere("ResellersCenter_Transactions.amountin",    "LIKE", "%$filter%")
                      ->orWhere("tblclients.id",            "LIKE", "%$filter%")
                      ->orWhere("tblclients.firstname",     "LIKE", "%$filter%")
                      ->orWhere("tblclients.lastname",      "LIKE", "%$filter%");
            });
        }
        
        $query->select("ResellersCenter_Transactions.*")
                ->addSelect(DB::raw("CONCAT('#', tblclients.id, ' ', tblclients.firstname, ' ', tblclients.lastname) as client"))
                ->addSelect(DB::raw("CONCAT(tblcurrencies.prefix, ResellersCenter_Transactions.amountin, tblcurrencies.suffix) as amountin"));
     
        $displayAmount = $query->count();
        $query->take($dtRequest->limit)->skip($dtRequest->offset);
        $query->orderBy($dtRequest->columns[$dtRequest->orderBy], $dtRequest->orderDir);
        $data = $query->get();

        return ["data" => $data, "displayAmount" => $displayAmount,"totalAmount" => $totalCount];
    }

    public function getTransactionsBalanceByInvoiceIds($invoicesIds)
    {
        $model = $this->getModel();
        return $model->select(DB::raw('(SUM(amountin - amountout)) as balance'))
            ->whereIn('invoice_id', $invoicesIds)
            ->get()
            ->sum('balance');
    }
}
