<?php

namespace MGModule\ResellersCenter\core\emailTemplatesFields\fields;

use MGModule\ResellersCenter\core\emailTemplatesFields\AbstractField;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\repository\whmcs\Invoices;

class InvoiceFields extends AbstractField
{

    function getParams($reseller, $client, $message, $relid):?array
    {
        return $this->fields->getFieldsValues($reseller->id, $client->id, null, $relid);
    }

    function getAttachments($relid)
    {
        $invoices = new Invoices();
        return $invoices->createAttachment($relid);
    }

    function getReceiverEmail($params)
    {
        return null;
    }

    function getReseller($relid, $message):?Reseller
    {
        //Search for relations with reseller in invoice items
        $repo = new Invoices();
        $invoice = $repo->find($relid);

        return new Reseller($invoice->getReseller());
    }

    function getClient($relid)
    {
        $repo = new Invoices();
        $invoice = $repo->find($relid);
        return $invoice->client;
    }
}