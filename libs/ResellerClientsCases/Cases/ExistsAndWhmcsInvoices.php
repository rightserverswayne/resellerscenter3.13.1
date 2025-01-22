<?php

namespace MGModule\ResellersCenter\libs\ResellerClientsCases\Cases;

use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\libs\ResellerClientsCases\ExistsCase;
use MGModule\ResellersCenter\models\CreditLine;
use MGModule\ResellersCenter\models\CreditLineHistory;
use MGModule\ResellersCenter\models\whmcs\Order;
use MGModule\ResellersCenter\repository\whmcs\Orders;
use \Illuminate\Database\Capsule\Manager as DB;

class ExistsAndWhmcsInvoices extends ExistsCase
{
    public function getInvoicesFromWhmcsInvoices($invoices)
    {
        $availableIds = $this->getAvailableInvoicesIds();
        $invoicesList = $invoices ?: [];

        return array_filter($invoicesList, function ($invoice) use ($availableIds){
            return in_array($invoice['id'], $availableIds);
        });
    }

    public function getInvoicesCounters()
    {
        $query = $this->invoicesRepo->getResellerRelatedClientInvoicesQuery(Session::get('uid'));
        return $this->invoicesRepo->getInvoicesCountersFromQuery($query);
    }

    public function getUnpaidInvoicesOverdueCount()
    {
        $query = $this->invoicesRepo->getResellerRelatedClientInvoicesQuery(Session::get('uid'));

        return $this->invoicesRepo->getUnpaidInvoicesOverdueCountFromQuery($query);
    }

    public function getAllOrdersActivatedByCreditLine($userId)
    {
        $historiesTable = (new CreditLineHistory())->getTable();
        $creditLinesTable = (new CreditLine())->getTable();
        $ordersTable = (new Order())->getTable();
        $invoicesTable = $this->invoicesRepo->getModel()->getTable();
        $invoiceItemsTable = $this->invoicesRepo->getInvoiceItemsRepo()->getModel()->getTable();

        return CreditLineHistory::select($ordersTable.'.id', $ordersTable.'.amount',
            DB::raw("CONCAT('viewinvoice.php?id=', {$invoicesTable}.id) as link"), $ordersTable.'.date')
            ->join($creditLinesTable, $creditLinesTable.'.id', '=', $historiesTable.'.credit_line_id')
            ->join($invoiceItemsTable, $invoiceItemsTable.'.id', '=', $historiesTable.'.invoice_item_id')
            ->join($invoicesTable, $invoicesTable.'.id', '=', $invoiceItemsTable.'.invoiceid')
            ->join($ordersTable, $ordersTable.'.invoiceid', '=', $invoicesTable.'.id')
            ->where($creditLinesTable.'.client_id', $userId)
            ->where($invoicesTable.'.status', $this->invoicesRepo::STATUS_UNPAID)
            ->where($ordersTable.'.status', Orders::STATUS_ACTIVE)
            ->groupBy($invoicesTable.'.id')
            ->get();
    }
}