<?php

namespace MGModule\ResellersCenter\Helpers\ExportModels;

use \MGModule\ResellersCenter\repository\whmcs\Invoices as WHMCSInvoicesRepo;

class WHMCSInvoices extends BaseModel
{
    
    protected $fileHeaders = [
        0 => 'ID',
        1 => 'Invoice Number',
        2 => 'Create Date',
        3 => 'Due Date',
        4 => 'Amount',
        5 => 'Status',
        6 => 'Reseller ID'
    ];
    
    protected function parseData($clients)
    {
        $parsedData = [];

        foreach($clients as $client)
        {           
            $invoices = $this->getInvoicesForClient($client->client_id);
            $this->addInvoicesToData($invoices, $parsedData, $client);
        }

        return $parsedData;
    }
    
    protected function getInvoicesForClient($clientId)
    {
        $repo = new WHMCSInvoicesRepo();
        return $repo->getByClient($clientId);
    }
    
    protected function addInvoicesToData($invoices, &$data, $client)
    {
        foreach($invoices as $invoice)
        {
            $data[] = [
                0 => $invoice->id,
                1 => $invoice->invoicenum,
                2 => $invoice->date,
                3 => $invoice->duedate,
                4 => $invoice->subtotal,
                5 => $invoice->status,
                6 => $client->reseller_id
            ];
        }
    }
    
}
