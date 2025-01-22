<?php

namespace MGModule\ResellersCenter\Helpers\ExportModels;

use \MGModule\ResellersCenter\repository\whmcs\Transactions as WHMCSTransactionsRepo;
use \MGModule\ResellersCenter\models\Reseller;

class WHMCSTransactions extends BaseModel
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

        $clients = Reseller::get();
        
        foreach($clients as $client)
        {
            $transactions = $this->getTransactionsForReseller($client->id);
            $this->addTransactionsToData($transactions, $parsedData, $client);
        }

        return $parsedData;
    }
    
    protected function getTransactionsForReseller($resellerId)
    {
        $repo = new WHMCSTransactionsRepo();
        return $repo->getTransactionsByReseller($resellerId);
    }
    
    protected function addTransactionsToData($transactions, &$data, $client)
    {
        foreach($transactions as $transaction)
        {
            $data[] = [
                0 => $transaction->id,
                1 => $transaction->invoiceid,
                2 => $transaction->transid,
                3 => $transaction->amountin,
                4 => $transaction->gateway,
                5 => $transaction->fees,
                6 => $client->id
            ];
        }
    }
    
}
