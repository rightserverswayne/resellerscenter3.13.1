<?php

namespace MGModule\ResellersCenter\Helpers\ExportModels;

class Hosting extends Products
{
    protected $type = 'hosting';
    protected $fileHeaders = [
        0 => 'ID',
        1 => 'Service',
        2 => 'Domain',
        3 => 'Client',
        4 => 'Price',
        5 => 'Billing Cycle',
        6 => 'Reseller ID'
    ];
           
    protected function addServicesToData($services, &$data, $client)
    {
        foreach($services as $service)
        {
            $data[] = [
                0 => $service->id,
                1 => $service->name,
                2 => $service->domain,
                3 => $service->userid,
                4 => $service->amount,
                5 => $service->billingcycle,
                6 => $client->reseller_id
            ];
        }
    }
    
}
