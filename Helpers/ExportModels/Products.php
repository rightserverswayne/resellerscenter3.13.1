<?php


namespace MGModule\ResellersCenter\Helpers\ExportModels;

use \MGModule\ResellersCenter\repository\ResellersServices;
use MGModule\ResellersCenter\repository\ResellersClients;

class Products extends BaseModel
{
    protected $type = 'hosting';
        
    protected function parseData($clients)
    {
        $parsedData = [];
        
        foreach($clients as $client)
        {          
            $services = $this->getServicesForClient($client->id);
            $this->addServicesToData($services, $parsedData, $client);          
        }
        
        return $parsedData;
    }
    
    protected function getServicesForClient($clientId)
    {
        $repo = new ResellersServices();
        return $repo->getServicesForExport($clientId, $this->type);
    }
     
}
