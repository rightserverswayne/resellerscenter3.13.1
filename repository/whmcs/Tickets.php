<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;

use \MGModule\ResellersCenter\repository\ResellersClients;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Tickets
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Tickets extends AbstractRepository
{
    const STATUS_OPEN           = "Open";
    const STATUS_ANSWERED       = "Answered";
    const STATUS_CUSTOMER_REPLY = "Customer-Reply";
    const STATUS_CLOSED         = "Closed";
    const STATUS_ON_HOLD        = "On Hold";
    const STATUS_IN_PROGRESS    = "In Progress";

    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\Ticket';
    }

    public function getNotAssigned($clientid = null, $limit = null)
    {
        $query = DB::table("tbltickets");

        $query->whereNotExists(function($query)
        {
            $query->from("ResellersCenter_ResellersTickets")->where("ResellersCenter_ResellersTickets.ticket_id", DB::raw("tbltickets.id"));
        });

        if(!empty($clientid))
        {
            $query->where("userid", $clientid);
        }

        $result = $query->take($limit)->get();
        return $result;
    }
    
    public function getResellerTickets($resellerid)
    {
        $rc = new ResellersClients();
        $clients = $rc->getByResellerId($resellerid);
        
        $clientsids = array();
        foreach($clients as $client) {
            $clientsids[] = $client->id;
        }

        $ticket = DB::table("tbltickets");
        $ticket->whereIn("userid", $clientsids);
        
        $result = $ticket->get();
        return $result;
    }

    public function getCount($userid, $active = 1, $admin = 0)
    {
        $model = $this->getModel();
        $query = $model->select("tbltickets.*")
                        ->where("userid", $userid)
                        ->getQuery();

        $query->leftJoin("tblticketstatuses", "tbltickets.status", "=", "tblticketstatuses.title");
        $query->where("tblticketstatuses.showactive", $active);

        $query->leftJoin("ResellersCenter_ResellersTickets", "ResellersCenter_ResellersTickets.ticket_id", "=", "tbltickets.id");
        if($admin)
        {
            $query->whereNull("ResellersCenter_ResellersTickets.ticket_id");
        }
        else
        {
            $query->whereNotNull("ResellersCenter_ResellersTickets.ticket_id");
        }

        return $query->count();
    }

    public function getClientTicket($clientid)
    {
        $query = DB::table("tbltickets");

        $query->select('tbltickets.id', 'tid', 'userid', 'title as subject','status','tblticketdepartments.name as department', 'lastreply', 'c');
        $query->leftJoin('tblticketdepartments', 'tblticketdepartments.id', '=', 'tbltickets.did');

        $query->where("userid", $clientid);

        return $query->get();
    }
      
//    public function getResellerTicketsForTable($resellerid, $dtRequest)
//    {
//        $dtCols = array("priority", "department", "client", "status", "lastreply");
//
//        //Get Resellers Clients
//        $rc = new ResellersClients();
//        $clients = $rc->getByResellerId($resellerid);
//        
//        $clientsids = array();
//        foreach($clients as $client) {
//            $clientsids[] = $client->id;
//        }
//
//        //Get Tickets
//        $query = DB::table("tbltickets");
//        $query->leftJoin("tblclients", function($join){
//            $join->on("tblclients.id", "=", "tbltickets.userid");
//        });
//        $query->leftJoin("tblticketdepartments", function($join){
//            $join->on("tblticketdepartments.id", "=", "tbltickets.did");
//        });
//        
//        $query->whereIn("userid", $clientsids);
//        $totalAmount = $query->count();
//        
//        //Apply Filters
//        if(!empty($dtRequest->filter))
//        {
//            $filter = $dtRequest->filter;
//            $query->where(function($query) use ($filter){
//                $query->orWhere("tbltickets.tid",       "LIKE", "%$filter%")
//                      ->orWhere("tbltickets.title",     "LIKE", "%$filter%")
//                      ->orWhere("tblclients.id",        "LIKE", "%$filter%")
//                      ->orWhere("tblclients.firstname", "LIKE", "%$filter%")
//                      ->orWhere("tblclients.lastname",  "LIKE", "%$filter%")
//                      ->orWhere("tblticketdepartments.name", "LIKE", "%$filter%");
//            });
//        }
//        
//        $query->select("tbltickets.*", DB::raw("Concat(tblclients.firstname, ' ', tblclients.lastname) as client"), 
//                       "tblticketdepartments.name as department", "tbltickets.urgency as priority", "tbltickets.title as subject");
//        
//        $query->take($dtRequest->limit)->skip($dtRequest->offset);
//        $query->orderBy($dtCols[$dtRequest->orderBy], $dtRequest->orderDir);
//        $result = $query->get();
//        
//        return array(
//            "data" => $result,
//            "displayAmount" => count($result),
//            "totalAmount" => $totalAmount
//        );
//    }

}
