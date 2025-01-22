<?php

namespace MGModule\ResellersCenter\repository;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller as ResellerObj;
use MGModule\ResellersCenter\libs\CreditLine\Helpers\CreditLineOperationLogger;
use MGModule\ResellersCenter\libs\CreditLine\Interfaces\InvoiceItemModelInterface as InvoiceItem;
use MGModule\ResellersCenter\libs\CreditLine\Interfaces\InvoiceModelInterface as Invoice;
use MGModule\ResellersCenter\libs\ResellerClientsCases\CasesFactory;
use MGModule\ResellersCenter\models\CreditLineHistory;
use MGModule\ResellersCenter\Repository\Source\AbstractRepository;

use \Illuminate\Database\Capsule\Manager as DB;

class CreditLineHistories extends AbstractRepository
{
    function determinateModel()
    {
        return CreditLineHistory::class;
    }

    public function hasUnpaidCreditForInvoiceItem(InvoiceItem $invoiceItem): bool
    {
        $model = $this->getModel();
        $log = $model
            ->where('invoice_item_id', $invoiceItem->id)
            ->where('invoice_type', $invoiceItem->invoice->getType())
            ->get();
        return $log->count() > 0 && $log->sum('amount') + $invoiceItem->amount == 0;
    }

    public function getAllOrdersActivatedByCreditLine($userId)
    {
        $resellerClientsRepo = new ResellersClients();
        $reseller = new ResellerObj($resellerClientsRepo->getResellerIdByHisClientId($userId));

        $case = CasesFactory::getExistCaseFromReseller($reseller);
        return $case->getAllOrdersActivatedByCreditLine($userId);
    }

    public function getDataForTable($dtRequest)
    {
        $dtCols = ["ResellersCenter_CreditLineHistory.id", "client", "creditLineId", "balance", "amount", "invoiceItemId","invoiceId", "invoiceType", "date"];
        $query = DB::table("ResellersCenter_CreditLineHistory");

        //Credit Line
        $query->leftJoin("ResellersCenter_CreditLine", "ResellersCenter_CreditLine.id", "=", "ResellersCenter_CreditLineHistory.credit_line_id");

        $query->leftJoin("ResellersCenter_InvoiceItems", function($join)
        {
            $join->on("ResellersCenter_InvoiceItems.id", "=", "ResellersCenter_CreditLineHistory.invoice_item_id");
            $join->where("ResellersCenter_CreditLineHistory.invoice_type", "=", "reseller");
        });

        $query->leftJoin("tblinvoiceitems", function($join)
        {
            $join->on("tblinvoiceitems.id", "=", "ResellersCenter_CreditLineHistory.invoice_item_id");
            $join->where("ResellersCenter_CreditLineHistory.invoice_type", "=", "whmcs");
        });

        //Client
        $query->leftJoin("tblclients", "tblclients.id", "=", "ResellersCenter_CreditLine.client_id");

        //Currency
        $query->leftJoin("tblcurrencies", "tblcurrencies.id", "=", "tblclients.currency");

        //Apply global search
        $filter = $dtRequest->filter;
        if(!empty($filter)) {
            $query->where(function($query) use ($filter) {
                $query->where('ResellersCenter_CreditLineHistory.amount', "LIKE", "%$filter%");
                $query->orWhere('tblclients.firstname', "LIKE", "%$filter%");
                $query->orWhere('tblclients.lastname', "LIKE", "%$filter%");
            });
        }

        $query->select(
            "ResellersCenter_CreditLineHistory.id",
            DB::raw("CONCAT(tblclients.firstname, ' ', tblclients.lastname) as client"),
            DB::raw("ResellersCenter_CreditLine.id as creditLineId"),
            DB::raw("CONCAT(tblcurrencies.prefix, ResellersCenter_CreditLineHistory.balance, tblcurrencies.suffix) as balance"),
            DB::raw("CONCAT(tblcurrencies.prefix, ResellersCenter_CreditLineHistory.amount, tblcurrencies.suffix) as amount"),
            DB::raw("ResellersCenter_CreditLineHistory.invoice_item_id as invoiceItemId"),
            DB::raw("IFNULL(ResellersCenter_InvoiceItems.invoice_id, tblinvoiceitems.invoiceid)  as invoiceId"),
            DB::raw("invoice_type as invoiceType"),
            DB::raw("ResellersCenter_CreditLine.client_id as clientId"),
            "ResellersCenter_CreditLineHistory.date");

        $displayAmount = $query->count();

        $query->orderBy($dtCols[$dtRequest->orderBy], $dtRequest->orderDir);
        $query->take($dtRequest->limit)->skip($dtRequest->offset);
        $data = $query->get();

        return [
            "data" => $data,
            "displayAmount" => $displayAmount,
            "totalAmount" => DB::table("ResellersCenter_CreditLineHistory")->count()
        ];
    }

    public function removeInvoiceLogs(Invoice $invoice)
    {
        $model = $this->getModel();

        $itemsIds = $invoice->items->pluck('id')
            ->toArray();

        $model->whereIn('invoice_item_id', $itemsIds)->where('invoice_type', CreditLineOperationLogger::WHMCS_INVOICE_TYPE )->delete();
    }

    public function wasActivatedByCreditLine(InvoiceItem $invoiceItem): bool
    {
        $model = $this->getModel();
        $log = $model
            ->where('invoice_item_id', $invoiceItem->id)
            ->where('invoice_type', $invoiceItem->invoice->getType())
            ->first();
        return (bool)$log->exists;
    }
}