<?php

namespace MGModule\ResellersCenter\repository\whmcs;

use \MGModule\ResellersCenter\repository\source\AbstractRepository;
use MGModule\ResellersCenter\models\whmcs\TicketReply;
use MGModule\ResellersCenter\models\whmcs\Ticket;

/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class TicketReplies extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\TicketReply';
    }

    public function getRepliesByTicketId($ticketid, $reply = false)
    {
        if($reply)
        {
            $model = new TicketReply();
            $replies = $model->where("tid", $ticketid)->orderBy("date", "desc")->get();
        }
        else
        {
            $model = new Ticket();
            $replies = $model->where("id", $ticketid)->get();
        }

        //Format messages
        foreach($replies as &$reply)
        {
            $editor = $reply->editor;
            $contentType = $reply ? "ticket_reply" : "ticket_msg";
            $markup = new \WHMCS\View\Markup\Markup();
            $markupFormat = $markup->determineMarkupEditor($contentType, $editor);
            $reply->message = $markup->transform($reply->message, $markupFormat, true);
        }

        return $replies;
    }

    public function getLastRawTicketReply($ticketId)
    {
        $model = new TicketReply();
        return $model->where("tid", $ticketId)->orderBy("date", "desc")->get()->first();
    }
}
