<?php

namespace MGModule\ResellersCenter\core\mergeFields\fields;

use MGModule\ResellersCenter\Core\Helpers\DateFormatHelper;
use MGModule\ResellersCenter\core\mergeFields\AbstractField;
use MGModule\ResellersCenter\mgLibs\Helpers\DateFormatter;
use MGModule\ResellersCenter\repository\whmcs\TicketReplies;
use MGModule\ResellersCenter\repository\whmcs\Tickets;

class TicketIdField extends AbstractField
{
    function getRelatedFields($value, $fields, $args = [])
    {
        if (!function_exists("getStatusColour"))
        {
            require_once ROOTDIR.DS."includes".DS."ticketfunctions.php";
        }

        $result = [];

        $repo = new Tickets();
        $ticket = $repo->find($value);

        if (!$ticket->exists) {
            return $result;
        }

        foreach ($fields["ticket_related"] as $key => $variable)
        {
            if ($key == "message" && $args['reply'])
            {
                $repliesRepo = new TicketReplies();
                $lastReply = $repliesRepo->getLastRawTicketReply($value);

                $ticket->message = $lastReply->message;
            }

            $name = substr(trim($variable, "}"), 2);
            $result[$name] = $ticket->{$key};
        }

        $result["department"] = $result["ticket_department"] = $ticket->department->name;
        $result["ticket_url"] = $this->getBrandedUrl($ticket->clientRC->reseller, "viewticket.php", ["tid" => $ticket->tid, "c" => $ticket->c]);
        $result["ticket_link"] = "<a href='{$result["ticket_url"]}'>{$result["ticket_url"]}</a>";
        $result["auto_close_time"] = $result[$name] = $ticket->replyingtime;
        $result['ticket_status'] = getStatusColour($result['ticket_status']);

        if (DateFormatHelper::changeDateFormatIsAllowed())
        {
            $dateFormat = $this->getResellerDateFormat($args['resellerId']);
            $dateFormatter = new DateFormatter();
            $result["ticket_date_opened"] = $dateFormatter->format($result["ticket_date_opened"], $dateFormat);
            $result["auto_close_time"] = $dateFormatter->format($result["auto_close_time"], $dateFormat);
            $result["ticket_kb_auto_suggestions"] = $dateFormatter->format($result["ticket_kb_auto_suggestions"], $dateFormat);
        }

        $markup = new \WHMCS\View\Markup\Markup();
        $markupFormat = $markup->determineMarkupEditor("ticket_msg", $ticket->editor);
        $result['ticket_message'] = $markup->transform($result['ticket_message'], $markupFormat, true);

        return $result;
    }
}