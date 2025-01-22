<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MGModule\ResellersCenter\Helpers\ExportModels;

/**
 * Description of Domains
 *
 * @author Admin
 */
class Domains extends Products
{
    
    protected $type = 'domain';
    protected $fileHeaders = [
        0 => 'ID',
        1 => 'Domain',
        2 => 'Client',
        3 => 'Recurring Amount',
        4 => 'Registration Period',
        5 => 'Reseller ID'
    ];
    
    protected function addServicesToData($services, &$data, $client)
    {
        foreach($services as $service)
        {
            $data[] = [
                0 => $service->id,
                1 => $service->domain,
                2 => $service->userid,
                3 => $service->recurringamount,
                4 => $service->registrationperiod,
                5 => $client->reseller_id
            ];
        }
    }
}
