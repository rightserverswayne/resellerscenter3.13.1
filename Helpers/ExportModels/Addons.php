<?php


namespace MGModule\ResellersCenter\Helpers\ExportModels;


class Addons extends Products
{
    protected $type = 'addon';
    
    protected $fileHeaders = [
        0 => 'ID',
        1 => 'Addon',
        2 => 'Product/Service',
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
                0 => $service->addonid,
                1 => $service->name,
                2 => $service->hostingid,
                3 => $service->userid,
                4 => $service->firstpaymentamount,
                5 => $service->billingcycle,
                6 => $client->reseller_id
            ];
        }
    }
    
}
