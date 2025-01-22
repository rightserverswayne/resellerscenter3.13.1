<?php

namespace MGModule\ResellersCenter\core\mergeFields\fields;

use MGModule\ResellersCenter\Core\Helpers\DateFormatHelper;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\core\mergeFields\AbstractField;
use MGModule\ResellersCenter\mgLibs\Helpers\DateFormatter;
use MGModule\ResellersCenter\repository\whmcs\Invoices;
use MGModule\ResellersCenter\repository\whmcs\PaymentGateways;

class InvoiceIdField extends AbstractField
{
    function getRelatedFields($value, $fields, $args = [])
    {
        $result = [];

        $repo = new Invoices();
        $invoice = $repo->find($value);
        foreach ($fields["invoice_related"] as $key => $variable) {
            $name = substr(trim($variable, "}"), 2);
            $result[$name] = $invoice->{$key};
        }

        if ($invoice->branded) {
            $result["invoice_num"] = $invoice->branded->invoicenum ?: $invoice->id;
        } else {
            $result["invoice_num"] = $invoice->invoicenum ?: $invoice->id;
        }

        $result['invoice_subtotal'] = $result['invoice_subtotal'] ? formatCurrency($result['invoice_subtotal'], $invoice->client->currency) : $result['invoice_subtotal'];
        $result['invoice_tax'] = $result['invoice_tax'] ? formatCurrency($result['invoice_tax'], $invoice->client->currency) : $result['invoice_tax'];
        $result['invoice_credit'] = $result['invoice_credit'] ? formatCurrency($result['invoice_credit'], $invoice->client->currency) : $result['invoice_credit'];
        $result['invoice_total'] = $result['invoice_total'] ? formatCurrency($result['invoice_total'], $invoice->client->currency) : $result['invoice_total'];

        $result["invoice_amount_paid"] = formatCurrency($invoice->amountpaid + $invoice->credit, $invoice->client->currency);
        $result["invoice_balance"] = formatCurrency($invoice->total - ($invoice->amountpaid + $invoice->credit), $invoice->client->currency);
        $result["invoice_items"] = $invoice->items->toArray();
        $result["invoice_html_contents"] = $this->getInvoiceHtmlLines($invoice);
        $viewinvoiceUrl = $this->getBrandedUrl($invoice->client->resellerClient->reseller, "viewinvoice.php", ["id" => $invoice->id]);
        $result["invoice_link"] = "<a href='{$viewinvoiceUrl}'>{$viewinvoiceUrl}</a>";

        $transaction = $invoice->transactions->last();
        $result["invoice_last_payment_amount"] = formatCurrency($transaction->amountin - $transaction->amountout, $invoice->client->currency);
        $result["invoice_last_payment_transid"] = $transaction->transid;

        $gatewayRepo = new PaymentGateways();
        $gateway = $gatewayRepo->getGatewaySettings($result['invoice_payment_method']);
        $result['invoice_payment_method'] = $gateway['name'];

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