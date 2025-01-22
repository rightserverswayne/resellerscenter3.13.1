<?php

namespace MGModule\ResellersCenter\core\mergeFields\fields;

use MGModule\ResellersCenter\Core\Helpers\DateFormatHelper;
use MGModule\ResellersCenter\core\mergeFields\AbstractField;
use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice as ResellersCenterInvoice;
use MGModule\ResellersCenter\mgLibs\Helpers\DateFormatter;

class RcinvoiceIdField extends AbstractField
{
    function getRelatedFields($value, $fields, $args = [])
    {
        $result = [];

        $invoice = new ResellersCenterInvoice($value);
        $invoice->load();

        foreach( $fields['invoice_related'] as $key => $variable )
        {
            $name          = substr(trim($variable, '}'), 2);
            $result[$name] = $invoice->{$key};
        }

        $gateway = $invoice->getPaymentMethod();
        if ($gateway) {
            $result['invoice_payment_method'] = $gateway->displayName;
        }

        $result['invoice_id']     = $invoice->id;
        $result['invoice_num']    = $invoice->invoicenum ?: $invoice->id;
        $result['invoice_status'] = $invoice->status;

        $result['invoice_amount_paid']   = formatCurrency($invoice->amountpaid + $invoice->credit, $invoice->client->currency);
        $result['invoice_balance']       = formatCurrency($invoice->total - ($invoice->amountpaid + $invoice->credit), $invoice->client->currency);
        $result['invoice_items']         = $invoice->items->toArray();
        $result['invoice_html_contents'] = $this->getInvoiceHtmlLines($invoice);
        $viewinvoiceUrl                  = $this->getBrandedUrl($invoice->client->resellerClient->reseller, 'rcviewinvoice.php', ['id' => $invoice->id]);
        $result['invoice_link']          = "<a href='{$viewinvoiceUrl}'>{$viewinvoiceUrl}</a>";

        $transaction                            = $invoice->transactions->last();
        $result['invoice_last_payment_amount']  = formatCurrency($transaction->amountin - $transaction->amountout, $invoice->client->currency);
        $result['invoice_last_payment_transid'] = $transaction->transid;

        if (DateFormatHelper::changeDateFormatIsAllowed()) {
            $dateFormat = $this->getResellerDateFormat($args['resellerId']);
            $dateFormatter = new DateFormatter();
            $result["invoice_date_created"] = $dateFormatter->format($result["invoice_date_created"], $dateFormat);
            $result["invoice_date_due"] = $dateFormatter->format($result["invoice_date_due"], $dateFormat);
            $result["invoice_date_paid"] = $dateFormatter->format($result["invoice_date_paid"], $dateFormat);
        }

        return $result;
    }
}