<?php

namespace MGModule\ResellersCenter\core\emailTemplatesFields\fields;

use MGModule\ResellersCenter\core\emailTemplatesFields\AbstractField;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\core\hooks\EmailPreSend;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\models\whmcs\Client;
use MGModule\ResellersCenter\repository\whmcs\Clients;
use MGModule\ResellersCenter\repository\whmcs\Invoices;

class GeneralFields extends AbstractField
{
    function getParams($reseller, $client, $message, $relid):?array
    {
        $params = $this->fields->getFieldsValues($reseller->id, $client->id);
        if ($message->name == EmailPreSend::ORDER_CONFIRMATION_TYPE) {
            $params = array_merge($params, $this->fields->getOrderRelatedFields($client->id));
        }
        return $params;
    }

    function getAttachments($relid)
    {
        return null;
    }

    function getReceiverEmail($params)
    {
        return null;
    }

    function getReseller($relid, $message):?Reseller
    {
        return ResellerHelper::getCurrent();
    }

    function getClient($relid)
    {
        $relid = ResellerHelper::isMakingOrderForClient() ? Session::get("makeOrderFor") : $relid;

        $repo = new Clients();
        return $repo->find($relid);
    }
}