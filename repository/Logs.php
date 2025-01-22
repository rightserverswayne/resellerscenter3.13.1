<?php
namespace MGModule\ResellersCenter\repository;
use MGModule\ResellersCenter\repository\source\AbstractRepository;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Invoices
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Logs extends AbstractRepository
{
    const INFO = 'info';
    
    const WARNING = 'warning';
    
    const ERROR = 'error';
    
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\Log';
    }
    
    public function createNew($type, $description, $adminid = 0, $resellerid = 0 , $clientid = 0)
    {
        $log = $this->getModel();
        $log->admin_id = $adminid;
        $log->reseller_id = $resellerid;
        $log->client_id = $clientid;
        $log->description = $description;
        $log->type = $type;
                
        $log->save();
    }
    
    public function getDataForTable($dtRequest)
    {
        $dtCols = array("id", "admin", "reseller", "client", "description", "type", "created_at"); 
        $query = DB::table("ResellersCenter_Logs");
        
        //Client
        $query->leftJoin("tblclients", "tblclients.id", "=", "ResellersCenter_Logs.client_id");
        
        //Reseller
        $query->leftJoin("ResellersCenter_Resellers", "ResellersCenter_Resellers.id", "=", "ResellersCenter_Logs.reseller_id");
        $query->leftJoin("tblclients as RClient", "RClient.id", "=", "ResellersCenter_Resellers.client_id");
        
        //Admin
        $query->leftJoin("tbladmins", "tbladmins.id", "=", "ResellersCenter_Logs.admin_id");
        
        //Apply global search
        $filter = $dtRequest->filter;
        if(!empty($filter)) {
            $query->where(function($query) use ($filter) {
                $query->where('ResellersCenter_Logs.description', "LIKE", "%$filter%");
                $query->orWhere('RClient.firstname', "LIKE", "%$filter%");
                $query->orWhere('RClient.lastname', "LIKE", "%$filter%");
                $query->orWhere('tblclients.firstname', "LIKE", "%$filter%");
                $query->orWhere('tblclients.lastname', "LIKE", "%$filter%");
                $query->orWhere('tbladmins.username', 'LIKE', "%$filter%");
            });
        }
        $query->select(
                DB::raw("CONCAT(RClient.firstname, ' ', RClient.lastname) as reseller"),
                DB::raw("CONCAT(tblclients.firstname, ' ', tblclients.lastname) as client"),
                DB::raw("tbladmins.username as admin"),
                "ResellersCenter_Logs.*");

        $displayAmount = $query->count();
        
        $query->orderBy($dtCols[$dtRequest->orderBy], $dtRequest->orderDir);
        $query->take($dtRequest->limit)->skip($dtRequest->offset);        
        $data = $query->get();
        
        return array(
            "data" => $data,
            "displayAmount" => $displayAmount,
            "totalAmount" => DB::table("ResellersCenter_Logs")->count()
        );
    }
}
