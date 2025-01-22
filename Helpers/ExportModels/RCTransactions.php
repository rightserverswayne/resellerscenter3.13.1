<?php

namespace MGModule\ResellersCenter\Helpers\ExportModels;

use \MGModule\ResellersCenter\repository\Transactions as RCTransactionsRepo;

class RCTransactions extends BaseModel
{
    
    protected $fileHeaders = [
        0 => 'ID',
        1 => 'Invoice ID',
        2 => 'Transaction ID',
        3 => 'Amount',
        4 => 'Gateway',
        5 => 'Fee',
        6 => 'Reseller ID'
    ];
    
    protected function parseData($clients)
    {
        $parsedData = [];


        foreach($clients as $client)
        {
            $transactions = $this->getTransactionsForClient($client->client_id);
            $this->addTransactionsToData($transactions, $parsedData, $client);
        }

        return $parsedData;
    }
    
    protected function getTransactionsForClient($clientId)
    {
        $repo = new RCTransactionsRepo();
        return $repo->getByClientId($clientId);
    }
    
    protected function addTransactionsToData($transactions, &$data, $client)
    {
        foreach($transactions as $transaction)
        {
            $data[] = [
                0 => $transaction->id,
                1 => $transaction->invoice_id,
                2 => $transaction->transid,
                3 => $transaction->amountin,
                4 => $transaction->gateway,
                5 => $transaction->fees,
                6 => $client->reseller_id
            ];
        }
    }
    
}
