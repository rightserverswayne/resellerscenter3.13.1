<?php

namespace MGModule\ResellersCenter\libs\CreditLine\Helpers;

use MGModule\ResellersCenter\libs\CreditLine\Interfaces\InvoiceItemModelInterface as InvoiceItem;
use MGModule\ResellersCenter\models\CreditLine;
use MGModule\ResellersCenter\repository\CreditLineHistories;
use MGModule\ResellersCenter\models\InvoiceItem as RcInvoiceItemModel;

class CreditLineOperationLogger
{
    const RESELLER_INVOICE_TYPE = 'reseller';
    const WHMCS_INVOICE_TYPE = 'whmcs';

    public static function logAddCredit(InvoiceItem $invoiceItem, CreditLine $creditLine)
    {
        self::logOperation($invoiceItem, $creditLine, true);
    }

    public static function logAddPayment(InvoiceItem $invoiceItem, CreditLine $creditLine)
    {
        self::logOperation($invoiceItem, $creditLine, false);
    }

    protected static function logOperation(InvoiceItem $invoiceItem, CreditLine $creditLine, $isCredit)
    {
        $repo = new CreditLineHistories();
        $total = abs($invoiceItem->amount);
        $data['credit_line_id'] = $creditLine->id;
        $data['balance'] = $creditLine->usage;
        $data['invoice_type'] = is_a($invoiceItem, RcInvoiceItemModel::class) ? self::RESELLER_INVOICE_TYPE : self::WHMCS_INVOICE_TYPE;
        $data['amount'] = $isCredit ? -($total) : $total;
        $data['invoice_item_id'] = $invoiceItem->id;
        $repo->create($data);
    }

}