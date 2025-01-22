<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Clients extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Client';
    }
    
    public function getLastest()
    {
        $model = $this->getModel();
        $client = $model->orderBy("id", "desc")->first();
        
        return $client;
    }
    
    public function getDecryptedCreditCard($userid)
    {
        global $cc_encryption_hash;
        $cchash = md5($cc_encryption_hash.$userid);
        
        $query = DB::table("tblclients");
        $query->select(
                "cardtype", 
                "cardlastfour",
                DB::raw("AES_DECRYPT(cardnum,'" . $cchash . "') as cardnum"),
                DB::raw("AES_DECRYPT(startdate,'" . $cchash . "') as startdate"),
                DB::raw("AES_DECRYPT(expdate,'" . $cchash . "') as expdate"),
                DB::raw("AES_DECRYPT(issuenumber,'" . $cchash . "') as issuenumber")
            );
        
        $query->where("id", $userid);
        return $query->first();
    }
    
    public function getAvailableClients($filter, $limit)
    {
        $query = DB::table("tblclients")->select(array("tblclients.id", "tblclients.firstname", "tblclients.lastname"));
        $query->whereNotExists(function($query){
            $query->select()
                ->from("ResellersCenter_ResellersClients")
                ->where("ResellersCenter_ResellersClients.client_id" ,"tblclients.id");
        });
        
        $query->whereNotExists(function($query){
            $query->from("ResellersCenter_ResellersClients")
                  ->where("ResellersCenter_ResellersClients.client_id", DB::raw("tblclients.id"));
        });
        
        $query->whereNotExists(function($query){
            $query->from("ResellersCenter_Resellers")
                  ->where("ResellersCenter_Resellers.client_id", DB::raw("tblclients.id"));
        });
        
        //Apply filter
        if(!empty($filter))
        {
            $query->where(function($query) use ($filter){
                $query->orWhere("tblclients.id",        "LIKE", "%$filter%")
                      ->orWhere("tblclients.firstname", "LIKE", "%$filter%")
                      ->orWhere("tblclients.lastname",  "LIKE", "%$filter%");
            });
        }

        $result = $query->take($limit)->get();

        return $result;
    }
}
