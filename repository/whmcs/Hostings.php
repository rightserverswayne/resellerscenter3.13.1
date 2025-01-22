<?php

namespace MGModule\ResellersCenter\repository\whmcs;

use MGModule\ResellersCenter\repository\source\AbstractRepository;
use \MGModule\ResellersCenter\models\whmcs\Hosting;

use MGModule\ResellersCenter\repository\ResellersServices;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Hostings
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Hostings extends AbstractRepository
{
    const STATUS_ACTIVE     = 'Active';
    const STATUS_PENDING    = 'Pending';
    const STATUS_TERMINATED = 'Terminated';
    const STATUS_SUSPENDED  = 'Suspended';
    
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Hosting';
    }
       
    public function getHostingById($hid)
    {
        $hosting = new Hosting();
        $result = $hosting->find($hid);
        
        return $result;
    }
    
    public function reassign($hostingid, $clientid)
    {
        $model = new Hosting();
        $hosting = $model->findOrFail($hostingid);

        $hosting->userid = $clientid;
        $hosting->save();
    }
    
    public function getNotAssigned($filter, $limit)
    {
        $query = DB::table("tblhosting");
        $query->leftJoin("tblproducts", function($join){
            $join->on("tblhosting.packageid", "=", "tblproducts.id");
        });
        
        $query->whereNotExists(function($query){
            $query->from("ResellersCenter_ResellersServices")
                    ->where("ResellersCenter_ResellersServices.relid", DB::raw("tblhosting.id"))
                    ->where("ResellersCenter_ResellersServices.type", ResellersServices::TYPE_HOSTING);
        });
        
        if(!empty($filter))
        {
            $query->where(function($query) use($filter){
                $query->orWhere("tblhosting.id", "LIKE", "%$filter%")
                      ->orWhere("tblhosting.domain", "LIKE", "%$filter%")
                      ->orWhere("tblproducts.name", "LIKE", "%$filter%");
            });
        }

        $query->select("tblhosting.*", DB::raw("tblproducts.name as product_name"));
        $result = $query->take($limit)->get();

        return $result;
    }
    
    public function getByOrderId($orderid)
    {
        $model = $this->getModel();
        $result = $model->where("orderid", $orderid)->get();
        
        return $result;
    }

    public function getNotAssignedIdsByUserId($userId):array
    {
        $query = DB::table("tblhosting");

        $query->whereNotExists(function($query){
            $query->from("ResellersCenter_ResellersServices")
                ->where("ResellersCenter_ResellersServices.relid", DB::raw("tblhosting.id"))
                ->where("ResellersCenter_ResellersServices.type", ResellersServices::TYPE_HOSTING);
        });

        $query->where('userid', $userId);


        $result = $query->select("tblhosting.id")->get();
        $ids = [];
        foreach ($result as $row) {
            $ids[] = $row->id;
        }

        return $ids;
    }

    public function getAssignedIdsByUserId($userId):array
    {
        $query = DB::table("tblhosting");

        $query->whereExists(function($query){
            $query->from("ResellersCenter_ResellersServices")
                ->where("ResellersCenter_ResellersServices.relid", DB::raw("tblhosting.id"))
                ->where("ResellersCenter_ResellersServices.type", ResellersServices::TYPE_HOSTING);
        });

        $query->where('userid', $userId);


        $result = $query->select("tblhosting.id")->get();
        $ids = [];
        foreach ($result as $row) {
            $ids[] = $row->id;
        }

        return $ids;
    }

    public function getCount($userid, $status, $admin = 0)
    {
        $model = $this->getModel();
        $query = $model->select("tblhosting.*")
            ->where("userid", $userid)
            ->where("domainstatus", $status)
            ->getQuery();

        $query->leftJoin("ResellersCenter_ResellersServices", function ($join)
        {
            $join->on("ResellersCenter_ResellersServices.relid",    "=", "tblhosting.id");
            $join->where("ResellersCenter_ResellersServices.type",  "=", ResellersServices::TYPE_HOSTING);
        });

        if ($admin) {
            $query->whereNull("ResellersCenter_ResellersServices.id");
        } else {
            $query->whereNotNull("ResellersCenter_ResellersServices.id");
        }

        return $query->count();
    }

}
