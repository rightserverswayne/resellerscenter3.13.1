<?php
namespace MGModule\ResellersCenter\repository;
use MGModule\ResellersCenter\repository\source\AbstractRepository;
use Illuminate\Database\Capsule\Manager as DB;

use MGModule\ResellersCenter\models\ResellerProfit;

/**
 * Description of ContentsSettings
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ResellersProfits extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\ResellerProfit';
    }
    
    public function createNew($resellerid, $invoiceItemid, $serviceid, $amount, $collected = 0)
    {
        $profit = new ResellerProfit();
        $profit->reseller_id = $resellerid;
        $profit->invoiceitem_id = $invoiceItemid;
        $profit->service_id = $serviceid;
        $profit->amount = $amount;
        $profit->collected = $collected;
        
        $profit->save();
        
        return $profit;
    }
    
    public function setAsCollected($profitid)
    {
        $profit = $this->find($profitid);
        $profit->collected = 1;
        $profit->save();
    }
    
    public function getProfitsByReseller($resellerid, $start = null, $end = null)
    {
        $query = DB::table("ResellersCenter_ResellersProfits");
        $query->where("reseller_id", $resellerid);

        if(!empty($start)) {
            $query->where("created_at", ">", $start); 
        }
        
        if(!empty($end)) {
            $query->where("created_at", "<", $end); 
        }
        
        return $query->get();
    }
    
    /**
     * Get Profits data formated for table
     * 
     * @param type $dtRequest
     */
    public function getDataForTable($dtRequest)
    {
        $dtCols = array("id", "firstname", "lastname", "companyname", "invoice", "description", "amount", "status", "created_at"); 
        
        $query = DB::table("ResellersCenter_ResellersProfits");
        $query->leftJoin("ResellersCenter_Resellers", function($join){
            $join->on("ResellersCenter_Resellers.id", "=", DB::raw("ResellersCenter_ResellersProfits.reseller_id"));
        });
        $query->leftJoin("ResellersCenter_ResellersSettings", function($join){
            $join->on("ResellersCenter_ResellersSettings.reseller_id", "=", DB::raw("ResellersCenter_ResellersProfits.reseller_id"));
            $join->on("ResellersCenter_ResellersSettings.setting", "=", DB::raw("'paypalAutoTransfer'"));
            $join->on("ResellersCenter_ResellersSettings.private", "=", DB::raw("'0'"));
        });

        $query->leftjoin('tblclients', function($join){
            $join->on("tblclients.id", "=", DB::raw("ResellersCenter_Resellers.client_id"));
        });
        $query->leftjoin('tblinvoiceitems', function($join){
            $join->on("tblinvoiceitems.id", "=", DB::raw("ResellersCenter_ResellersProfits.invoiceitem_id"));
        });
        $query->leftjoin('tblinvoices', function($join){
            $join->on("tblinvoices.id", "=", DB::raw("tblinvoiceitems.invoiceid"));
        });
        
        //Apply global search
        $filter = $dtRequest->filter;
        if(!empty($filter)){
            $query->where(function($query) use ($filter){
                $query->where("tblclients.firstname", "LIKE", "%$filter%")
                      ->orWhere("tblclients.lastname", "LIKE", "%$filter%")
                      ->orWhere("tblclients.companyname", "LIKE", "%$filter%")
                      ->orWhere("tblinvoiceitems.description", "LIKE", "%$filter%")
                      ->orWhere("tblinvoices.invoicenum", "LIKE", "%$filter%");
            });
        }
        
        $query->select(
            "ResellersCenter_ResellersProfits.id", "tblclients.firstname", "tblclients.lastname", "tblclients.companyname",
            DB::raw("(CASE tblinvoices.invoicenum WHEN '' THEN tblinvoices.id ELSE tblinvoices.invoicenum END) as invoice"), 
            "tblinvoiceitems.description", "ResellersCenter_ResellersProfits.amount", DB::raw("tblinvoices.id as invoice_id"),
            DB::raw("ResellersCenter_ResellersProfits.collected as status"), "ResellersCenter_ResellersProfits.created_at",
            DB::raw("ResellersCenter_Resellers.id as resellerid"), 
            DB::raw("ResellersCenter_ResellersSettings.value as paypaltransfer")
        );
        
        $displayAmount = $query->count();
        $query->orderBy($dtCols[$dtRequest->orderBy], $dtRequest->orderDir);
        $query->take($dtRequest->limit)->skip($dtRequest->offset);
        
        $data = $query->get();

        return array(
            "data" => $data,
            "displayAmount" => $displayAmount,
            "totalAmount" => DB::table("ResellersCenter_ResellersProfits")->count()
        );
    }
}