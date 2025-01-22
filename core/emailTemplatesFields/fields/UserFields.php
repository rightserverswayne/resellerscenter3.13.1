<?php

namespace MGModule\ResellersCenter\core\emailTemplatesFields\fields;

use MGModule\ResellersCenter\core\emailTemplatesFields\AbstractField;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\repository\whmcs\Users;

class UserFields extends AbstractField
{

    function getParams($reseller, $client, $message, $relid):?array
    {
        return $this->fields->getFieldsValues($reseller->id, $client->id, null, null, null, null, null, null, $relid);
    }

    function getAttachments($relid)
    {
        return null;
    }

    function getReceiverEmail($params)
    {
        return $params['userEmail'];
    }

    function getReseller($relid, $message):?Reseller
    {
        return ResellerHelper::getCurrent();
    }

    function getClient($relid)
    {
        $userRepo = new Users();
        return $userRepo->find($relid);
    }
}