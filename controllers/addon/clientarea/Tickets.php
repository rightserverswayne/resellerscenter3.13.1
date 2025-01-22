<?php

namespace MGModule\ResellersCenter\controllers\addon\clientarea;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;
use MGModule\ResellersCenter\repository\ResellersSettings;
use MGModule\ResellersCenter\repository\whmcs\Tickets as TicketRepo;
use MGModule\ResellersCenter\repository\ResellersTickets;
use MGModule\ResellersCenter\core\Session;
use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\core\datatable\DatatableDecorator as Datatable;
use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;
use MGModule\ResellersCenter\core\Request;

/**
 * Description of Tickets
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Tickets extends AbstractController
{
    public function indexHTML()
    {
        return [
            'tpl'   => 'base',
            'vars' => []
        ];
    }
    
    public function deleteTicketJSON()
    {
        $ticketid = Request::get("ticketid");
        
        //Remove ticket relation
        $repo = new ResellersTickets();
        $repo->deleteByTicketId($ticketid);
        
        return ["success" => Lang::T('delete','success')];
    }
    
    public function getTicketsForTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();
        $reseller = Reseller::getLogged();
        
        $tickets = new ResellersTickets();
        $result = $tickets->getResellerTicketsForTable($reseller->id, $dtRequest);
        
        $format = array("status" => array(
            "class" => array(array("Open", "Open"), array("Answered", "Answered"), array("Customer-Reply", "Customer-Reply"), array("On Hold", "OnHold"),  array("In Progress", "InProgress"), array("Closed", "Closed"))
        ));
        
        $buttons = array(
            array(
                "type" => "only-icon", 
                "class" => "openDetailsTicket btn-primary", 
                "data" => array("ticketid" => "id"), 
                "icon" => "fa fa-pencil-square-o",
                "tooltip" => Lang::T('table','detailsInfo')
            ),
            
            array(
                "type" => "only-icon", 
                "class" => "openDeleteTicket btn-danger", 
                "data" => array("ticketid" => "id"), 
                "icon" => "fa fa-trash-o",
                "tooltip" => Lang::T('table','deleteInfo')
            )
        );
        
        $datatable = new Datatable($format, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }
    
    
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Details
     */
    public function detailsHTML()
    {
        $ticketid = Request::get("tid");
        $reseller = Reseller::getLogged();
        global $CONFIG;
        //Check if client ticket belongs to current Reseller
        $ticket = $reseller->tickets->find($ticketid);
        if(!$ticket->exists)
        {
            return [
                'tpl'  => 'details/base',
                'vars' => [
                    'error'                  => 'Ticket not found',
                    'TicketNotFound'         => 1
                ]
            ];
        }
        
        //Add response to the ticket
        if (Request::get("message")) {
            $operatorName = $reseller->client->firstname . ' ' . $reseller->client->lastname;
            $this->addResponse($ticket, Request::get("message"), $reseller->client->id, $operatorName);
        }
        
        $services = $reseller->hosting->getByClient($ticket->userid);

        return [
            'tpl'  => 'details/base',
            'vars' => [
                'ticket'                 => $ticket,
                'services'               => $services,
                'resellerData'           => $reseller->client,
                'TicketAllowedFileTypes' => str_replace(',', ', ', $CONFIG['TicketAllowedFileTypes'])
            ]
        ];
    }

    public function changeStatusJSON()
    {
        $status = Request::get("status");
        $ticketid = Request::get("ticketid");
        
        $repo = new TicketRepo();
        $ticket = $repo->find($ticketid);
        
        $ticket->updateStatus($status);
        return array("success" => Lang::T('status','success'));
    }
    
    private function addResponse($ticket, $message, $clientId, $operatorName = null)
    {
        //UploadFiles
        require_once ROOTDIR."/includes/ticketfunctions.php";
        $attachments = \uploadTicketAttachments();
        Session::set("attachments", $attachments);

        $params = [
            "ticketid" => $ticket->id,
            "message"  => $message,
            "markdown" => true,
            "clientid" => $clientId,
            'attachments' => $attachments
        ];

        if (!empty($operatorName)) {
            $params["adminusername"] = $operatorName;
        }
        
        //Send message
        $result = WhmcsAPI::request('AddTicketReply', $params);
        
        /**
         * After reply is added hook EmailPreSend is running
         * and function addAttachmentsToTicketReply add uploaded
         * attachments
         */

        if ($result["result"] != "success") {
            throw new Exception("Unable to send ticket reply. ". $result["message"]);
        }

        $repo = new TicketRepo();
        $ticket = $repo->find($ticket->id);
        $ticket->updateStatus(TicketRepo::STATUS_ANSWERED);
        
        EventManager::call("newTicketReply", $ticket->id);
    }

    public function isActive()
    {
        $reseller = Reseller::getLogged();
        $settingsRepo = new ResellersSettings();
        $settings = $settingsRepo->getSettings($reseller->id);

        return !empty($settings['ticketDeptids']);
    }
    
}
