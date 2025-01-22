<?php

namespace MGModule\ResellersCenter\core\mergeFields;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\repository\whmcs\Configuration;

abstract class AbstractField
{
    abstract function getRelatedFields($value, $fields, $args = []);

    protected function getBrandedUrl($model, $path = "", $query = array())
    {
        global $CONFIG;
        $reseller   = new Reseller($model);
        $configRepo = new Configuration();

        $whmcsURL = parse_url($CONFIG["SystemURL"]);
        if ($reseller->settings->admin->cname && $reseller->settings->private->domain) {
            $resellerDomain = rtrim($reseller->settings->private->domain,'/');
            if (isset($whmcsURL["path"])) {
                $resellerDir    =  trim($whmcsURL["path"], '/');
                $domain         = "{$whmcsURL["scheme"]}://{$resellerDomain}/{$resellerDir}";
            } else {
                $domain         = "{$whmcsURL["scheme"]}://{$resellerDomain}";
            }
        } else {
            $systemUrl = $configRepo->getSetting('SystemURL');
            $domain = rtrim("{$systemUrl}", '/');
            $query = array_merge($query, array("resid" => $reseller->id));
        }

        $queryString = http_build_query($query);
        $path = ltrim($path, '/');
        $url = "{$domain}/{$path}?{$queryString}";

        return $url;
    }

    protected function getInvoiceHtmlLines($invoice)
    {
        global $whmcs;
        $currencyid = $invoice->client->currency;

        $invoicedescription = "";
        foreach ($invoice->items as $item) {
            $invoicedescription .= $item->description . " " . formatCurrency($item->amount). "<br>";
        }

        $invoicedescription .= "------------------------------------------------------<br>";
        $invoicedescription .= $whmcs->get_lang('invoicessubtotal') . ": " . formatCurrency($invoice->subtotal, $currencyid) . "<br>";

        if ($invoice->taxrate != 0) {
            $invoicedescription .= $invoice->taxrate . "% " . $invoice->client->tax->name. ": " . formatCurrency($invoice->tax, $currencyid) . "<br>";
        }

        if ($invoice->taxrate2 != 0) {
            $invoicedescription .= $invoice->taxrate2 . "% " . $invoice->client->tax2->name . ": " . formatCurrency($invoice->tax2, $currencyid) . "<br>";
        }

        $invoicedescription .= $whmcs->get_lang('invoicescredit') . ": " . formatCurrency($invoice->credit, $currencyid) . "<br>";
        $invoicedescription .= $whmcs->get_lang('invoicestotal') . ": " . formatCurrency($invoice->total, $currencyid);

        return $invoicedescription;
    }

    protected function getResellerDateFormat($id)
    {
        if ($id != null) {
            $reseller = \MGModule\ResellersCenter\Core\Helpers\Reseller::createById($id);
            return $reseller->settings->private->dateFormat;
        }
        return null;
    }
}