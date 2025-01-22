<?php

namespace MGModule\ResellersCenter\Helpers\ExportModels;

use \MGModule\ResellersCenter\models\whmcs\Client;
use \MGModule\ResellersCenter\repository\ResellersClients;

class Clients extends BaseModel
{
    
    protected $fileHeaders = [
        0 => 'ID',
        1 => 'First Name',
        2 => 'Last Name',
        3 => 'Company Name',
        4 => 'Income',
        5 => 'Assigned Since',
        6 => 'Reseller ID'
    ];
               
    protected function parseData($clients)
    {
        $parsedData = [];

        foreach($clients as $client)
        {        
            $parsedData[] = [
                0 => (string)$client->client_id,
                1 => $client->firstname,
                2 => $client->lastname,
                3 => $client->companyname,
                4 => $this->getIncomeForClient($client->client_id),
                5 => $client->datecreated,
                6 => $client->reseller_id
            ];
        }
        
        return $parsedData;      
    }
    
    protected function getIncomeForClient($id)
    {
        $repo = new ResellersClients();
        return $repo->getIncomeFromClient($id);
    }
   
}
