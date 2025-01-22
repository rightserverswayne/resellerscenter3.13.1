<?php

namespace MGModule\ResellersCenter\Helpers;

class ExportCSV
{

    protected $type;
    protected $resellerId;
    protected $recordsData;
    protected $file;

    public function __construct(string $type, ?int $resellerId)
    {
        $this->type       = $type;
        $this->resellerId = $resellerId;
    }

    public function download()
    {
        $this->createFileContent();
        header("Content-Description: File Transfer");
        header("Content-Type:application/csv");
        header("Content-disposition: attachment; filename=\"" . $this->type . ".csv\"");
        echo stream_get_contents($this->file);
        exit;
    }

    private function createFileContent()
    {
        $this->file = fopen('php://output', 'w');

        fputcsv($this->file, $this->getFirstLine(), ';');

        foreach ($this->recordsData as $record)
        {
            fputcsv($this->file, $record, ';');
        }
    }

    private function getFirstLine()
    {
        $class             = 'MGModule\ResellersCenter\Helpers\ExportModels\\' . $this->type;
        $typeClass         = new $class($this->resellerId);
        $this->recordsData = $typeClass->getRecords();

        return $typeClass->getCSVHeaders();
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }
}
