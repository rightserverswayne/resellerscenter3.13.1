<?php 
namespace MGModule\ResellersCenter\repository\whmcs;
use MGModule\ResellersCenter\repository\source\AbstractRepository;

use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Promotions
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Promotions extends AbstractRepository
{
    const TYPE_PERCENTAGE = "Percentage";
    const TYPE_FIXED      = "Fixed Amount";
    const TYPE_OVERRIDE   = "Price Override";
    const TYPE_FREE_SETUP = "Free Setup";
    
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Promotion';
    }
           
    public function getByCode($code)
    {
        $model = $this->getModel();
        $promotion = $model->where("code", $code)->first();
        
        return $promotion;
    }
    
    /**
     * Get promotions data for table in Reseller Area.
     * Prefix contains reseller id to find correct records
     * 
     * @param type $dtRequest
     * @param type $prefix
     */
    public function getForTable($dtRequest, $prefix, $filter)
    {
        $query = DB::table("tblpromotions");
        $query->where("code", "LIKE", "{$prefix}%");
        $totalAmount = $query->count();
        
        if(!empty($dtRequest->filter))
        {
            $filter = $dtRequest->filter;
            $query->where(function($query) use ($filter){
                $query->orWhere("tblpromotions.code",      "LIKE", "%$filter%")
                      ->orWhere("tblpromotions.type",      "LIKE", "%$filter%")
                      ->orWhere("tblpromotions.value",     "LIKE", "%$filter%")
                      ->orWhere("tblpromotions.startdate", "LIKE", "%$filter%")
                      ->orWhere("tblpromotions.expirationdate", "LIKE", "%$filter%");
            });
        }
        
        if($filter == "active")
        {
            $query->where(function($query){
                $query->orWhere("tblpromotions.expirationdate", ">=", date("Y-m-d"))
                      ->orWhere("tblpromotions.expirationdate", "=", "0000-00-00");
            });
            $query->where(function($query){
                $query->orWhere("tblpromotions.maxuses", ">", DB::raw("tblpromotions.uses"))
                      ->orWhere("tblpromotions.maxuses", "=", "0");
            });
        }
        elseif($filter == "expired")
        {
            $query->where(function($query)
            {
                $query->orWhere(function($query){
                    $query->where("tblpromotions.expirationdate", "<", date("Y-m-d"))
                          ->where("tblpromotions.expirationdate", "!=", "0000-00-00");
                });
                $query->orWhere(function($query){
                    $query->where("tblpromotions.uses", ">=", DB::raw("tblpromotions.maxuses"))
                          ->where("tblpromotions.maxuses", "!=", "0");                          
                });
            });
        }
        
        $displayAmount = $query->count();
        $query->select("*")
              ->addSelect(DB::raw("REPLACE(tblpromotions.code, '{$prefix}', '') as code"))
              ->addSelect(DB::raw("IF(tblpromotions.startdate = '0000-00-00', 'N/A', tblpromotions.startdate) as startdate"))
              ->addSelect(DB::raw("IF(tblpromotions.expirationdate = '0000-00-00', 'N/A', tblpromotions.expirationdate) as expirationdate"));
        
        $query->take($dtRequest->limit)->skip($dtRequest->offset);
        $query->orderBy($dtRequest->columns[$dtRequest->orderBy], $dtRequest->orderDir);
        $result = $query->get();
        
        return ["data" => $result, "displayAmount" => $displayAmount, "totalAmount" => $totalAmount];
    }
}