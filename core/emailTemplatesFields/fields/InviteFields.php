<?php

namespace MGModule\ResellersCenter\core\emailTemplatesFields\fields;

use MGModule\ResellersCenter\core\emailTemplatesFields\AbstractField;
use MGModule\ResellersCenter\models\whmcs\Client;
use MGModule\ResellersCenter\repository\whmcs\Clients;
use MGModule\ResellersCenter\repository\whmcs\Invites;

class InviteFields extends AbstractField
{

    function getParams($reseller, $client, $message, $relid):?array
    {
       return $this->fields->getFieldsValues($reseller->id, $client->id, null, null, null, null, null, $relid);
    }

    function getAttachments($relid)
    {
        return null;
    }

    function getReceiverEmail($params)
    {
        return $params['invitationEmail'];
    }

    function getReseller($relid, $message)
    {
        return null;
    }

    function getClient($relid)
    {
        $repo = new Invites();
        $clientId = $repo->find($relid)->client_id;
        $clientRepo = new Clients();
        return $clientRepo->find($clientId);
    }
}