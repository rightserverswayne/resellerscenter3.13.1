<?php

namespace MGModule\ResellersCenter\core\emailTemplatesFields;

use MGModule\ResellersCenter\core\MergeFields;

abstract class AbstractField
{
    protected $fields;

    public function __construct()
    {
        $this->fields = new MergeFields();
    }

    abstract function getParams($reseller, $client, $message, $relid);
    abstract function getAttachments($relid);
    abstract function getReceiverEmail($params);
    abstract function getReseller($relid, $message);
    abstract function getClient($relid);

}