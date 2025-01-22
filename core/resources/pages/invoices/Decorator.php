<?php

namespace MGModule\ResellersCenter\Core\Resources\Pages\Invoices;
use MGModule\ResellersCenter\gateways\DeferredPayments\DeferredPayments;
use MGModule\ResellersCenter\repository\PaymentGateways;
use MGModule\ResellersCenter\repository\Invoices;

use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice as ResellersCenterInvoice;
use MGModule\ResellersCenter\core\resources\gateways\Factory as PaymentGatewayFactory;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;

use MGModule\ResellersCenter\core\helpers\ClientAreaHelper;
use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\core\Server;

use MGModule\ResellersCenter\mgLibs\Smarty;
use MGModule\ResellersCenter\Addon;

/**
 * Description of Decorator
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Decorator
{
    /**
     * Get html code for invoice view
     *
     * @param \MGModule\ResellersCenter\Core\Resources\Invoices\Invoice $invoice
     * @return string HTML code for invoice view
     */
    public function getPageView(ResellersCenterInvoice $invoice, $vars)
    {
        global $whmcs;

        //Skip invoice parse
        if ($vars["invalidInvoiceIdRequested"])
        {
            $vars["LANG"] = $this->loadWhmcsLanguage($invoice->client->language);
            $view         = $this->parseInvoiceTemplate($invoice->reseller, $vars);
            return $view;
        }

        $params = $invoice->toArray();
        $params["invoiceid"]      = $invoice->id;
        
        $params["clientsdetails"] = $invoice->client->toArray();   
        $clientCustomFields = $this->getClientCustomFields($invoice->client->id, $invoice->client->customfields);
        $params["clientsdetails"] = array_merge($params["clientsdetails"], $clientCustomFields);
        
        $params["customfields"]   = $this->getVisibleClientCustomFields($invoice->client->id, $invoice->client->customfields);
        $params["transactions"]   = $invoice->transactions;
        $params["invoiceitems"]   = $invoice->items->toArray();
        $params["currency"]       = $invoice->client->currencyObj;
        
        if($params['tax'] != 0)
        {
            $params['taxname'] = $invoice->client->tax->name;
        }
        if($params['tax2'] != 0)
        {
            $params['taxname2'] = $invoice->client->tax2->name;
        }

        $params['total']        = formatCurrency($params['total'], $invoice->client->currency)->toNumeric();
        $params['credit']       = formatCurrency($params['credit'], $invoice->client->currency)->toNumeric();
        $params["balance"]     = formatCurrency($invoice->total - $invoice->amountpaid, $invoice->client->currency);
        $params["totalcredit"] = formatCurrency($invoice->client->credit, $invoice->client->currency)->toNumeric();

        if($invoice->status == Invoices::STATUS_UNPAID)
        {
            if($invoice->client->credit > 0)
            {
                $params["creditamount"] = $invoice->client->credit < $params["balance"]->toNumeric() ? $params["totalcredit"] : $params["balance"]->toNumeric();
                $params["allowCreditPayment"] = $invoice->reseller->settings->admin->allowCreditPayment;
                if($vars['applycredit'])
                {
                    //This overwritten the "creditamount" value in the form
                    unset($vars["creditamount"]);
                }
            }

            $params["allowchangegateway"] = 1;
            $params["gatewaydropdown"]    = $this->getGatewayDropdown($invoice->reseller->id, $invoice->paymentmethod);
            $params["paymentbutton"]      = $this->getPaymentButton($invoice->reseller->id, $invoice->paymentmethod, $invoice);
        }

        $params["pagetitle"] = $whmcs->get_lang("invoicenumber") . ($invoice->invoicenum ?: $invoice->id);
        $params["LANG"]      = $this->loadWhmcsLanguage($invoice->client->language);

        $params = array_merge($params, $vars);
        $view   = $this->parseInvoiceTemplate($invoice->reseller, $params);
        return $view;
    }

    private function getGatewayDropdown($resellerid, $selected)
    {
        $options = "";
        $gateways = Helper::getCustomGateways($resellerid);
        foreach ($gateways as $gateway)
        {
            if ($gateway->enabled && !is_a($gateway, DeferredPayments::class))
            {
                if ($gateway->compareName($selected))
                {
                    $options .= "<option value='{$gateway->getNormalisedName()}' selected>{$gateway->displayName}</option>";
                }
                else
                {
                    $options .= "<option value='{$gateway->getNormalisedName()}'>{$gateway->displayName}</option>";
                }
            }
        }

        return "<select class='form-control select-inline' name='gateway' onchange='submit()'>{$options}</select>";
    }

    private function getPaymentButton($resellerid, $gatewayName, $invoice)
    {
        $repo = new PaymentGateways();
        if (empty($gatewayName))
        {
            $gatewayName = $repo->getDefaultGateway($resellerid);
            if (empty($gatewayName))
            {
                die("Unexpected payment method value. Exiting.");
            }
        }

        $gateway = PaymentGatewayFactory::get($resellerid, $gatewayName);

        return $gateway->link($invoice);
    }

    private function getClientCustomFields($clientid, $fields)
    {
        $result = array();
        foreach ($fields as $key => $field)
        {
            $value  = $field->getValueByRelid($clientid);
            $i      = $key+1;
            $result["customfields"][] = array(
                "id"    => $field->id,
                "value" => $value,
            );
            $result["customfields{$i}"] = $value;
        }
        
        return $result;
    }

    private function getVisibleClientCustomFields( $clientid, $fields )
    {
        $result = [];
        foreach( $fields as $field )
        {
            $value = $field->getValueByRelid($clientid);
            if($field->showinvoice !== 'on' || empty($value))
            {
                continue;
            }

            $name = $field->fieldname;
            if( strpos($field->fieldname, '|') !== false )
            {
                $name = explode('|',$field->fieldname)[1];
            }

            $result[] = [
                'fieldname' => $name,
                'value'     => $value,
            ];
        }

        return $result;
    }

    private function loadWhmcsLanguage($language)
    {
        if (empty($language))
        {
            $language = \WHMCS\Config\Setting::getValue("Language");
        }

        $path = Addon::getWHMCSDIR() . "lang";
        require "{$path}/{$language}.php";

        return $_LANG;
    }

    private function parseInvoiceTemplate($model, $params)
    {
        global $CONFIG;

        $reseller    = new Reseller($model->id);
        $settings    = $reseller->settings->private;
        $templatedir = Addon::getWHMCSDIR() . DS . "templates";

        $path     = str_replace(basename(Server::get("SCRIPT_NAME")), "", Server::get("SCRIPT_NAME"));
        $template = $settings->whmcsTemplate ?: $CONFIG["Template"];

        //Branding and custom variables
        $required = array(
            "charset"       => $CONFIG["charset"],
            "BASE_PATH_CSS" => "{$path}assets/css",
            "template"      => $template,
            "companyname"   => $reseller->settings->private->companyName,
            "payto"         => $reseller->settings->private->payto
        );

        $dir = ClientAreaHelper::getLogoPath();

        if ($reseller->settings->private->logo) {
            $required["logo"] = "{$dir}{$reseller->settings->private->logo}";
        }

        if ($reseller->settings->private->showInvoiceLogo == 'on' && $reseller->settings->private->invoiceLogo) {
            $required["RCInvoiceLogo"] = "{$dir}{$reseller->settings->private->invoiceLogo}";
        }
        
        $countries = new \WHMCS\Utility\Country();
        $params['clientsdetails']['country'] = $countries->getName($params['clientsdetails']['country']);

        $final = array_merge($params, $required);
        $html  = Smarty::I()->view("$template/rcviewinvoice", $final, $templatedir);
        return $html;
    }
}
