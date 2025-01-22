<?php

namespace MGModule\ResellersCenter\core\emailTemplatesFields\fields;

use MGModule\ResellersCenter\core\emailTemplatesFields\AbstractField;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\repository\ResellersServices;
use MGModule\ResellersCenter\repository\whmcs\Domains;

class DomainFields extends AbstractField
{
    function getParams($reseller, $client, $message, $relid):?array
    {
        return $this->fields->getFieldsValues($reseller->id, $client->id, null, null, null, $relid);
    }

    function getAttachments($relid)
    {
        return null;
    }

    function getReceiverEmail($params)
    {
        return null;
    }

    function getReseller($relid, $message)
    {
        $repo = new ResellersServices();
        $service = $repo->getByTypeAndRelId(ResellersServices::TYPE_DOMAIN, $relid);
        return new Reseller($service->reseller->id);
    }

    function getClient($relid)
    {
        $repo = new Domains();
        $domain = $repo->find($relid);
        return $domain->client;
    }
}