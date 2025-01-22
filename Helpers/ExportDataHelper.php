<?php
namespace MGModule\ResellersCenter\Helpers;

class ExportDataHelper 
{
    
    const EXPORT_DATA_TYPES = [
        'Clients'           => 'Clients',
        'Hosting'           => 'Hosting',
        'Addons'            => 'Addons',
        'Domains'           => 'Domains',
        'Resellers Center Invoices' => 'RCInvoices',
        'WHMCS Invoices'    => 'WHMCSInvoices',
        'Resellers Center Transactions' => 'RCTransactions',
        'WHMCS Transactions' => 'WHMCSTransactions',
    ];
    
    
    public static function getExportDataTypes()
    {
        return self::EXPORT_DATA_TYPES;
    }
    
    
    public static function getDataForTable($dtRequest)
    {
        $results = self::parseDataForTable();

        $orderCol = $dtRequest->columns[$dtRequest->orderBy];

        usort($results,function($a, $b) use ($dtRequest, $orderCol)
        {
            if ($dtRequest->orderDir == 'asc') {
                return ($a->{$orderCol} < $b->{$orderCol}) ? -1 : 1;
            } else {
                return ($a->{$orderCol} > $b->{$orderCol}) ? -1 : 1;
            }
        });

        return [
            'data'          => $results,
            'displayAmount' => count(self::getExportDataTypes()),
            'totalAmount'   => count(self::getExportDataTypes())
        ];
    }
    
    private static function parseDataForTable()
    {
        $return = [];
        foreach(self::EXPORT_DATA_TYPES as $name => $type)
        {
            $obj = new \stdClass();
            $obj->data = $name;
            $return[] = $obj;
        }
        
        return $return;
    }
}
