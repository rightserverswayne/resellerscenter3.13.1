<?php

namespace MGModule\ResellersCenter\core\emailTemplatesFields\fields;

use MGModule\ResellersCenter\core\emailTemplatesFields\AbstractField;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\models\whmcs\Client;

class AdminFields extends AbstractField
{
    function getParams($reseller, $client, $message, $relid):?array
    {
        return null;
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
        return null;
    }

    function getClient($relid)
    {
        return null;
    }
}