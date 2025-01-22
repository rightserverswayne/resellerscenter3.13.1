<?php

namespace MGModule\ResellersCenter\Helpers\ExportModels;

use MGModule\ResellersCenter\models\Reseller;
use MGModule\ResellersCenter\repository\ResellersClients;

class BaseModel
{
    protected $recordsData = [];
    protected $resellerId;
    protected $fileHeaders;

    public function __construct(?int $resellerId)
    {
        $this->resellerId = $resellerId;
    }

    public function getCSVHeaders()
    {
        return $this->fileHeaders;
    }

    public function getRecords()
    {
        return $this->getResellersClients() ? $this->parseData($this->getResellersClients()) : [];
    }

    protected function getResellersClients()
    {
        return (new ResellersClients())->getByResellerId($this->resellerId);
    }
}