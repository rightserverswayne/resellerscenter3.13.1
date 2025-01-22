<?php

namespace MGModule\ResellersCenter\repository;

use \Illuminate\Database\Capsule\Manager as DB;
use MGModule\ResellersCenter\libs\GlobalSearch\SearchTypes;
use MGModule\ResellersCenter\models\whmcs\Ticket;
use MGModule\ResellersCenter\repository\source\AbstractRepository;

/**
 * Description of ResellersTickets
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ResellersTickets extends AbstractRepository 
{
    const BANNED_TICKET_MENU_ITEMS = ["Open Ticket", "Support Tickets"];

    public function determinateModel() 
    {
        return 'MGModule\ResellersCenter\models\ResellerTicket';
    }
    
    /**
     * Create new relation with Reseller
     * 
     * @param type $resellerid
     * @param type $relid
     * @param type $type
     */
    public function createNew($resellerid, $ticketid)
    {
        $model = $this->getModel();
        $model->reseller_id = $resellerid;
        $model->ticket_id = $ticketid;
        
        $model->save();
    }
    
    /**
     * 
     * @param type $resellerid
     */
    public function getByResellerId($resellerid)
    {
        $model = $this->getModel();
        $tickets = $model->where("reseller_id", $resellerid)->get();
        
        return $tickets;
    }

    public function getResellerTicketIdsByClientAndReseller($resellerId, $clientId):array
    {
        $result = $this->getResellerTicketsByClientAndReseller($resellerId, $clientId);

        $ids = [];
        foreach ($result as $row) {
            $ids[] = $row->id;
        }
        return $ids;
    }

    public function getResellerTicketTidsByClientAndReseller($resellerId, $clientId):array
    {
        $result = $this->getResellerTicketsByClientAndReseller($resellerId, $clientId);

        $ids = [];
        foreach ($result as $row) {
            $ids[] = $row->tid;
        }
        return $ids;
    }

    protected function getResellerTicketsByClientAndReseller($resellerId, $clientId)
    {
        $model   = $this->getModel();

        return $model->join("tbltickets", "tbltickets.id", "=", "ResellersCenter_ResellersTickets.ticket_id")
            ->where("ResellersCenter_ResellersTickets.reseller_id", $resellerId)
            ->where("tbltickets.userid", $clientId)
            ->get();
    }

    /**
     * Get ticket id
     *
     * @param $ticketid
     * @return mixed
     */
    public function getByRelId($ticketid, $type = null, $resellerid = null)
    {
        $model = $this->getModel();
        if($resellerid == null)
        {
            $ticket = $model->where("ticket_id", $ticketid)->first();
        }
        else
        {
            $ticket = $model->where("ticket_id", $ticketid)
                            ->where("reseller_id", $resellerid)
                            ->first();
        }

        return $ticket;
    }
    
    public function getByTicketTid($tid)
    {
        $query = DB::table("ResellersCenter_ResellersTickets");
        $query->join("tbltickets", function($join){
            $join->on("tbltickets.id", "=", "ResellersCenter_ResellersTickets.ticket_id");
        });
        
        $ticket = $query->where("tbltickets.tid", $tid)->first();
        
        //Load ResellerTicket 
        $model = $this->getModel();
        $result = $model->where("ticket_id", $ticket->id)->first();
        
        return $result; 
    }
    
    public function deleteByTicketId($ticketid)
    {
        $model = $this->getModel();
        $model->where("ticket_id", $ticketid)->delete();
    }
            
    public function getResellerTicketsForTable($resellerid, $dtRequest)
    {
        //Get Tickets
        $query = DB::table("ResellersCenter_ResellersTickets");
        $query->join("tbltickets", function($join){
            $join->on("tbltickets.id", "=", "ResellersCenter_ResellersTickets.ticket_id");
        });
        $query->leftJoin("tblclients", function($join){
            $join->on("tblclients.id", "=", "tbltickets.userid");
        });
        $query->leftJoin("tblticketdepartments", function($join){
            $join->on("tblticketdepartments.id", "=", "tbltickets.did");
        });
        
        $query->where("reseller_id", $resellerid);
        $totalAmount = $query->count();
        
        //Apply Filters
        if(!empty($dtRequest->filter))
        {
            $filter = $dtRequest->filter;
            $query->where(function($query) use ($filter){
                $query->orWhere("tbltickets.tid",       "LIKE", "%$filter%")
                      ->orWhere("tbltickets.title",     "LIKE", "%$filter%")
                      ->orWhere("tbltickets.urgency",   "LIKE", "%$filter%")
                      ->orWhere("tbltickets.status",    "LIKE", "%$filter%")
                      ->orWhere("tblclients.id",        "LIKE", "%$filter%")
                      ->orWhere("tblclients.firstname", "LIKE", "%$filter%")
                      ->orWhere("tblclients.lastname",  "LIKE", "%$filter%")
                      ->orWhere("tblticketdepartments.name", "LIKE", "%$filter%");
            });
        }
        
        $query->select("tbltickets.*", DB::raw("IFNULL(Concat(tblclients.firstname, ' ', tblclients.lastname), tbltickets.name) as client"),
                       "tblticketdepartments.name as department", "tbltickets.urgency as priority", "tbltickets.title as subject");
        
        $displayAmount = $query->count();
        $query->take($dtRequest->limit)->skip($dtRequest->offset);
        $query->orderBy($dtRequest->columns[$dtRequest->orderBy], $dtRequest->orderDir);
        $result = $query->get();
        
        return array(
            "data" => $result,
            "displayAmount" => $displayAmount,
            "totalAmount" => $totalAmount
        );
    }

    public function getTicketsForGlobalSearch($resellerId, $filter)
    {
        $model = $this->getModel();
        $rcTickets = $model->getTable();
        $whmcsTickets = (new Ticket())->getTable();

        $query = DB::table($rcTickets);

        $query->select($whmcsTickets.".id")
            ->addSelect(DB::raw('"'.SearchTypes::TICKET_TYPE.'" AS type'))
            ->addSelect(DB::raw($whmcsTickets. ".title as name"))
            ->addSelect(DB::raw($whmcsTickets. ".status"))
            ->addSelect(DB::raw($whmcsTickets. ".date"))
            ->addSelect(DB::raw($whmcsTickets. ".userid as client_id"));

        $query->where($rcTickets.'.reseller_id', $resellerId);

        $query->where(function($query) use($filter, $whmcsTickets)
        {
            $query->orWhere($whmcsTickets.".title", "LIKE", "%$filter%")
                ->orWhere($whmcsTickets.".id", "LIKE", "%$filter%");
        });

        $query->leftJoin($whmcsTickets, $whmcsTickets.".id", "=", $rcTickets.".ticket_id");

        return $query;
    }
}
