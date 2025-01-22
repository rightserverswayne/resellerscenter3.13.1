<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;
use MGModule\ResellersCenter\models\whmcs\HostingAddon;

use MGModule\ResellersCenter\repository\ResellersServices;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Addons
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class HostingAddons extends AbstractRepository
{    
    const STATUS_ACTIVE = "Active";
    const STATUS_PENDING = "Pending";
    const STATUS_SUSPENDED = "Suspended";
    const STATUS_TERMINATED = "Terminated";
    
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\HostingAddon';
    }
    
    public function getNotAssigned($filter, $limit)
    {
        $query = DB::table("tblhostingaddons");
        $query->leftJoin("tbladdons", function($join){
            $join->on("tbladdons.id", "=", "tblhostingaddons.addonid");
        });
        $query->leftJoin("tblhosting", function($join){
            $join->on("tblhosting.id", "=", "tblhostingaddons.hostingid");
        });
        
        $query->whereNotExists(function($query){
            $query->from("ResellersCenter_ResellersServices")
                  ->where("ResellersCenter_ResellersServices.relid", DB::raw("tblhostingaddons.id"))
                  ->where("ResellersCenter_ResellersServices.type", ResellersServices::TYPE_ADDON);
        });
        
        if(!empty($filter))
        {
            $query->where(function($query) use ($filter){
                $query->orWhere("tblhostingaddons.id", "LIKE", "%$filter%")
                      ->orWhere("tbladdons.name", "LIKE", "%$filter%")
                      ->orWhere("tblhosting.domain", "LIKE", "%$filter%");
            });
        }
        
        $query->select("tblhostingaddons.*", "tbladdons.name", "tblhosting.domain");
        $result = $query->take($limit)->get();
        
        return $result;
    }
    
    public function getByOrderId($orderid)
    {
        $model = $this->getModel();
        $result = $model->where("orderid", $orderid)->get();
        
        return $result;
    }

    public function getById($id)
    {
        $hostingAddon = new HostingAddon();
        $result = $hostingAddon->find($id);

        return $result;
    }



}
