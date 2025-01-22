<?php



namespace MGModule\ResellersCenter\Helpers;


class JsonExport
{
    protected $data;
    protected $type;
    protected $resellerId;
    protected $headers;

    public function __construct($type, $resellerId)
    {
        $this->type = $type;
        $this->resellerId = $resellerId;

        $this->assignData();
    }

    private function assignData()
    {
        $class = 'MGModule\ResellersCenter\Helpers\ExportModels\\' . $this->type;
        if(!class_exists($class))
        {
            throw new \Exception('Invalid export type');
        }
        $typeClass = new $class($this->resellerId);
        $this->headers = $typeClass->getCSVHeaders();
        $this->data = $typeClass->getRecords();
    }

    public function getJson()
    {
        return array_map([$this, 'assignHeaders'], $this->data);
    }

    public function assignHeaders($data)
    {
        $return = [];
        foreach($data as $header => $item)
        {
            $return[$this->headers[$header]] = $item;
        }
        return $return;
    }
}
