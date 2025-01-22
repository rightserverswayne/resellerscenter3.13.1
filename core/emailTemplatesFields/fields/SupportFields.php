<?php

namespace MGModule\ResellersCenter\core\emailTemplatesFields\fields;

use MGModule\ResellersCenter\core\emailTemplatesFields\AbstractField;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\core\hooks\EmailPreSend;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\repository\ResellersTickets;
use MGModule\ResellersCenter\repository\whmcs\Tickets;

class SupportFields extends AbstractField
{
    function getParams($reseller, $client, $message, $relid):?array
    {
        return $this->fields
            ->addAdditionalParam('reply', $message->name == EmailPreSend::SUPPORT_TICKET_REPLY)
            ->getFieldsValues($reseller->id, $client->id, $relid);
    }

    function getAttachments($relid)
    {
        return explode(",", Session::get("attachments"));
    }

    function getReceiverEmail($params)
    {
        return null;
    }

    function getReseller($relid, $message)
    {
        $repo = new ResellersTickets();
        $ticket = $repo->getByRelId($relid);
        $reseller = new Reseller($ticket->reseller->id);

        //Ticket Open hook runs after this one
        if (!$reseller->exists && $message->name == EmailPreSend::SUPPORT_TICKET_OPENED) {
            $reseller = ResellerHelper::getCurrent();
        }

        return $reseller;

    }

    function getClient($relid)
    {
        $repo = new Tickets();
        $ticket = $repo->find($relid);
        return $ticket->client;
    }
}