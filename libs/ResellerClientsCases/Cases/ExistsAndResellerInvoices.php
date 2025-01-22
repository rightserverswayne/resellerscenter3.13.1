<?php

namespace MGModule\ResellersCenter\libs\ResellerClientsCases\Cases;

use MGModule\ResellersCenter\Core\Resources\Invoices\View as InvoiceView;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\libs\ResellerClientsCases\ExistsCase;
use MGModule\ResellersCenter\models\CreditLine;
use MGModule\ResellersCenter\models\CreditLineHistory;
use MGModule\ResellersCenter\models\whmcs\Order;
use MGModule\ResellersCenter\repository\InvoiceItems;
use MGModule\ResellersCenter\repository\Invoices as RCInvoicesRepo;
use MGModule\ResellersCenter\repository\Transactions;
use MGModule\ResellersCenter\repository\whmcs\Invoices as WhmcsInvoicesRepo;
use MGModule\ResellersCenter\repository\whmcs\Orders;
use \Illuminate\Database\Capsule\Manager as DB;

class ExistsAndResellerInvoices extends ExistsCase
{
    protected $whmcsInvoicesRepo;

    public function __construct()
    {
        parent::__construct();
        $this->invoicesRepo = new RCInvoicesRepo();
        $this->whmcsInvoicesRepo = new WhmcsInvoicesRepo();
    }

    public function getInvoicesFromWhmcsInvoices($invoices)
    {
        $rcInvoices = [];
        $invoices = $this->invoicesRepo->getEndClientInvoicesByClient(Session::get('uid'));
        foreach ($invoices as $rcInvoiceRaw) {
            $rcInvoices[] = InvoiceView::getClientAreaPageList($rcInvoiceRaw);
        }
        return $rcInvoices;
    }

    public function getInvoicesCounters()
    {
        $query = $this->invoicesRepo->getEndClientInvoicesByClientQuery(Session::get('uid'));
        return $this->whmcsInvoicesRepo->getInvoicesCountersFromQuery($query, $this->invoicesRepo->getModel()->getTable());
    }

    public function getUnpaidInvoicesBalance()
    {
        return $this->invoicesRepo->getEndClientInvoicesBalance(Session::get('uid'));
    }

    public function getUnpaidInvoicesOverdueBalance()
    {
        return $this->invoicesRepo->getEndClientInvoicesOverdueBalance(Session::get('uid'));
    }

    public function getUnpaidInvoicesOverdueCount()
    {
        return $this->invoicesRepo->getEndClientInvoicesOverdueCount(Session::get('uid'));
    }

    protected function getAvailableInvoicesIds():array
    {
        return $this->availableIds ?:$this->invoicesRepo->getEndClientInvoicesIdsByClient(Session::get('uid'));
    }

    public function getInvoiceItemsFromWhmcsInvoiceItems($invoiceItems1)
    {
        //Get all unpaid invoices from client
        $invoices = $this->invoicesRepo->getByClientAndStatus(Session::get('uid'), RCInvoicesRepo::STATUS_UNPAID);

        $invoiceItems = [];

        foreach ($invoices as $invoice) {
            foreach ($invoice->items as $item) {
                if ($item->type == InvoiceItems::TYPE_INVOICE || ($invoice->total - $invoice->amountpaid) <= 0) {
                    break;
                }

                $invoiceItems[$invoice->id][] = [
                    "invoicenum" => $invoice->invoicenum,
                    "id" => $item->id,
                    "description" => $item->description,
                    "amount" => formatCurrency($item->amount)
                ];
            }
        }

        return $invoiceItems;
    }

    public function getInvoicesPartialPayments()
    {
        $transactionsRepo = new Transactions();
        return $transactionsRepo->getTransactionsBalanceByInvoiceIds($this->getAvailableInvoicesIds());
    }

    public function getInvoicesCredit()
    {
        return $this->invoicesRepo->getSummaryCreditByInvoiceIds($this->getAvailableInvoicesIds());
    }

    public function getInvoicesSubTotal($invoices)
    {
        return $this->invoicesRepo->getInvoicesSubTotalByInvoicesIds($this->getAvailableInvoicesIds());
    }

    public function getInvoicesTotal($invoices)
    {
        return $this->invoicesRepo->getInvoicesTotalByInvoicesIds($this->getAvailableInvoicesIds());
    }

    public function getInvoicesTotalTax($invoices)
    {
        return $this->invoicesRepo->getInvoicesTotalTaxByInvoicesIds($this->getAvailableInvoicesIds());
    }

    public function getInvoicesTotalTax2($invoices)
    {
        return $this->invoicesRepo->getInvoicesTotalTax2ByInvoicesIds($this->getAvailableInvoicesIds());
    }

    public function getParentInvoiceId()
    {
        return false;
    }

    public function getAllOrdersActivatedByCreditLine($userId)
    {
        $historiesTable = (new CreditLineHistory())->getTable();
        $creditLinesTable = (new CreditLine())->getTable();
        $ordersTable = (new Order())->getTable();
        $invoicesTable = $this->invoicesRepo->getModel()->getTable();
        $invoiceItemsTable = $this->invoicesRepo->getInvoiceItemsRepo()->getModel()->getTable();

        return CreditLineHistory::select($ordersTable.'.id', $ordersTable.'.amount',
            DB::raw("CONCAT('rcviewinvoice.php?id=', {$invoicesTable}.id) as link"), $ordersTable.'.date')
            ->join($creditLinesTable, $creditLinesTable.'.id', '=', $historiesTable.'.credit_line_id')
            ->join($invoiceItemsTable, $invoiceItemsTable.'.id', '=', $historiesTable.'.invoice_item_id')
            ->join($invoicesTable, $invoicesTable.'.id', '=', $invoiceItemsTable.'.invoice_id')
            ->join($ordersTable, $ordersTable.'.invoiceid', '=', $invoicesTable.'.relinvoice_id')
            ->where($creditLinesTable.'.client_id', $userId)
            ->where($invoicesTable.'.status', $this->whmcsInvoicesRepo::STATUS_UNPAID)
            ->where($ordersTable.'.status', Orders::STATUS_ACTIVE)
            ->groupBy($invoicesTable.'.id')
            ->get();
    }
}
