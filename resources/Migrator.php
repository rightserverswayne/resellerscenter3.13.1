<?php
namespace MGModule\ResellersCenter\resources;
use \Illuminate\Database\Capsule\Manager as DB;

use MGModule\ResellersCenter\repository\Groups;
use MGModule\ResellersCenter\repository\Resellers;
use MGModule\ResellersCenter\repository\ResellersClients;

use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;

/**
 * Description of Migrator
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Migrator 
{
    public function performMigration()
    {    
        try
        {
            $this->transferGroups();
            $this->transferResellers();
            $this->transferEndClients();
        }
        catch(\Exception $ex)
        {
            die($ex->getMessage());
        }
    }
    
    public function transferGroups()
    {
        $repo = new Groups();
        $groups = DB::table("reseller_productgrups")->get();
        foreach($groups as $group)
        {
            $repo->createNew($group->name);
        }
    }
    
    public function transferResellers()
    {
        $resellersRepo = new Resellers();

        $resellers = DB::table("reseller_resellers")->get();
        foreach($resellers as $reseller)
        {
            $relation = DB::table("reseller_grouprelation")->where("client_id", $reseller->reseller_id)->first();
            $oldGroup = DB::table("reseller_productgrups")->where("id", $relation->group_id)->first();
            
            $groupsRepo = new Groups();
            $group = $groupsRepo->where("name", "=", $oldGroup->name)->first();
            
            if(!empty($group->id))
            {
                $resellersRepo->createNew($reseller->reseller_id, $group->id);
            }
        }
    }
    
    public function transferEndClients()
    {
        $clientsRepo = new ResellersClients();
        
        $resellersRepo = new Resellers();
        $resellers = $resellersRepo->all();
        foreach($resellers as $reseller)
        {
            foreach($reseller->client->contacts as $contact)
            {
                $email = $contact->email;
                $contact->email = $contact->email . "-rc-migration";
                $contact->save();
                
                $client = new Client();
                $clientid = $client->create(array(
                    "firstname"     => $contact->firstname,
                    "lastname"      => $contact->lastname,
                    "companyname"   => $contact->comapnyname,
                    "email"         => $email,
                    "currency"      => $reseller->client->currency,
                    "phonenumber"   => $contact->phonenumber,
                    "address1"      => $contact->address1,
                    "address2"      => $contact->address2,
                    "country"       => $contact->country,
                    "state"         => $contact->state,
                    "postcode"      => $contact->postcode,
                    "city"          => $contact->city,
                    
                    //Send welcome msg
                    "sendWelcomeMsg" => "on",
                    "skipvalidation" => true
                ));
                
                $clientsRepo->createNew($reseller->id, $clientid);
            }
        }
    }
}