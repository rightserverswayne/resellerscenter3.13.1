<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\DateFormatHelper;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice as ResellersCenterInvoice;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\gateways\DeferredPayments\DeferredPayments;
use MGModule\ResellersCenter\mgLibs\Helpers\DateFormatter;
use MGModule\ResellersCenter\models\whmcs\Invoice;
use MGModule\ResellersCenter\repository\Invoices;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\repository\ResellersSettings;
use MGModule\ResellersCenter\repository\whmcs\Transactions;

class ClientAreaPageViewInvoice
{

    public $functions;

    public static $params;

    public function __construct()
    {
        $this->functions[10] = function($params) {
            self::$params = $this->checkAllowCreditPayment($params);
        };

        if (DateFormatHelper::changeDateFormatIsAllowed()) {
            $this->functions[20] = function($params) {
                self::$params = $this->setInvoiceData(self::$params);
            };

            $this->functions[30] = function($params) {
                self::$params = $this->setTransactions(self::$params);
            };

            $this->functions[40] = function($params) {
                self::$params = $this->setDateFormat(self::$params);
            };
        }

        $this->functions[50] = function($params) {
            self::$params = $this->unsetDeferredGateway(self::$params);
        };

        $this->functions[60] = function($params)
        {
            //Fix for WHMCS 7.2.1
            global $smartyvalues;
            $smartyvalues = self::$params;

            return self::$params;
        };
    }

    private function checkAllowCreditPayment($params)
    {
        $invoice = $this->getCurrentInvoice();

        if ($invoice->status != Invoices::STATUS_UNPAID) {
            return $params;
        }

        $reseller = ResellerHelper::getCurrent();
        $isResellerClient = (new ResellersClients())->getByRelid($invoice->userid)->exists;

        if ($reseller->exists && $reseller->settings->admin->resellerInvoice && $isResellerClient) {
            $params["manualapplycredit"] = $reseller->settings->admin->allowCreditPayment == 'on';
        }
        return $params;
    }

    private function setInvoiceData($params)
    {
        $models = $this->getCurrentInvoice();

        foreach ($models->getAttributes() as $key=>$value) {
            $params['invoiceData'][$key] = $value;
        }
        return $params;
    }

    private function setTransactions($params)
    {
        $params['transactions'] =  Transactions::getTransactionsArrayByInvoiceId($params['invoiceData']['id']);
        return $params;
    }

    private function setDateFormat($params)
    {
        $resellersClientsRepo = new ResellersClients();
        $resellerClient = $resellersClientsRepo->getByRelid($params['invoiceData']['userid']);

        $format = (new ResellersSettings())->getSetting('dateFormat', $resellerClient->reseller_id, true);

        $dateFormatter = new DateFormatter();

        $params['date'] = $dateFormatter->format($params['invoiceData']['date'], $format);
        $params['datedue'] = $dateFormatter->format($params['invoiceData']['datedue'], $format);

        foreach ($params['transactions'] as &$transaction) {
            $transaction['date'] = $dateFormatter->format($transaction['date'], $format);
        }

        return $params;
    }

    private function getCurrentInvoice()
    {
        $invoiceId = Request::get('id');
        return Invoice::find($invoiceId);
    }

    protected function unsetDeferredGateway($params)
    {
        $params['availableGateways'] = array_filter(($params['availableGateways'] ?: []), function ($key) {
            return strtolower($key) != DeferredPayments::SYS_NAME;
        }, ARRAY_FILTER_USE_KEY);

        $params['gateways'] = array_filter(($params['gateways'] ?: []), function ($value) {
            return strtolower($value['sysname']) != DeferredPayments::SYS_NAME;
        });

        return $params;
    }

}
