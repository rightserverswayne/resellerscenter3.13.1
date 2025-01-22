<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;
use MGModule\ResellersCenter\models\whmcs\Domain;

use MGModule\ResellersCenter\repository\ResellersServices;
use \Illuminate\Database\Capsule\Manager as DB;
/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Domains extends AbstractRepository
{
    const STATUS_ACTIVE = "Active";
    const STATUS_PENDING = "Pending";
    const STATUS_PENDING_TRANSFER = "Pending Transfer";
    const STATUS_PENDING_EXPIRED = "Expired";
    const STATUS_TRANSFERRED_AWAY = "Transferred Away";
    const STATUS_CANCELLED = "Cancelled";
    const STATUS_FRAUD = "Fraud";
    
    const TYPE_REGISTER = 'Register';
    const TYPE_TRANSFER = 'Transfer';
    
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Domain';
    }
    
    public function reassign($domainid, $clientid)
    {
        $model = new Domain();
        $domain = $model->findOrFail($domainid);
                
        $domain->userid = $clientid;
        $domain->save();
    }
    
    public function getNotAssigned($filter = null, $limit = null, $clientid = null)
    {
        $query = DB::table("tbldomains");
        
        $query->whereNotExists(function($query){
            $query->from("ResellersCenter_ResellersServices")
                  ->where("ResellersCenter_ResellersServices.relid", DB::raw("tbldomains.id"))
                  ->where("ResellersCenter_ResellersServices.type", ResellersServices::TYPE_DOMAIN);
        });
            
        if(!empty($filter))
        {
            $query->where(function($query) use($filter){
                $query->orWhere("tbldomains.id", "LIKE", "%$filter%")
                      ->orWhere("tbldomains.domain", "LIKE", "%$filter%");
            });
        }

        if(!empty($clientid))
        {
            $query->where("userid", $clientid);
        }

        $result = $query->take($limit)->get();
        return $result;
    }

    public function getCount($userid, $status, $admin = 0)
    {
        $model = $this->getModel();
        $query = $model->select("tbldomains.*")
                        ->where("userid", $userid)
                        ->where("status", $status)
                        ->getQuery();

        $query->leftJoin("ResellersCenter_ResellersServices", function ($join)
        {
            $join->on("ResellersCenter_ResellersServices.relid",    "=", "tbldomains.id");
            $join->where("ResellersCenter_ResellersServices.type",  "=", ResellersServices::TYPE_DOMAIN);
        });

        if($admin)
        {
            $query->whereNull("ResellersCenter_ResellersServices.id");
        }
        else
        {
            $query->whereNotNull("ResellersCenter_ResellersServices.id");
        }

        return $query->count();
    }

    public function getByOrderId($orderid)
    {
        $model = $this->getModel();
        $result = $model->where("orderid", $orderid)->get();
        
        return $result;
    }

    public function getByName($name)
    {
        $model = $this->getModel();
        $result = $model->where("domain", $name)->get();

        return $result;
    }

    public function getById($id)
    {
        $domain = new Domain();
        $result = $domain->find($id);

        return $result;
    }
}
