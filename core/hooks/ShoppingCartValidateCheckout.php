<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\libs\CreditLine\Exceptions\ClientLimitExceedException;
use MGModule\ResellersCenter\libs\CreditLine\Exceptions\NoAvailableCreditLineException;
use MGModule\ResellersCenter\libs\CreditLine\Exceptions\ResellerLimitExceedException;
use MGModule\ResellersCenter\libs\CreditLine\Exceptions\WrongPaymentMethodException;
use MGModule\ResellersCenter\libs\CreditLine\Services\SubServices\ResellerClientCreditLineService;
use MGModule\ResellersCenter\libs\CreditLine\Services\SubServices\ResellerCreditLineService;
use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\models\whmcs\Invoice as WhmcsInvoiceModel;
use MGModule\ResellersCenter\models\Invoice as RcInvoiceModel;

/**
 * Description of ShoppingCartValidateCheckout
 *
 * @author Paweł Złamaniec
 */
class ShoppingCartValidateCheckout 
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     * 
     * @var array 
     */
    public $functions;

    /**
     * Assign anonymous function
     */
    public function __construct()
    {
        $this->functions[10] = function($params)
        {
            return $this->forceTOS($params);
        };

        $this->functions[20] = function($params)
        {
            return $this->checkCreditLine($params);
        };
    }

    /**
     * Remove service relation
     * 
     * @param type $params
     * @return type
     */
    public function forceTOS($params)
    {
        global $CONFIG;

        $reseller = Reseller::getCurrent();
        if($reseller->exists && $reseller->settings->admin->branding)
        {
            if($reseller->settings->private->tos)
            {
                $CONFIG["EnableTOSAccept"] = "on";
                $CONFIG["TermsOfService"]  = $reseller->settings->private->tos;
            }
            else
            {
                $CONFIG["EnableTOSAccept"] = "";
            }
        }
    }

    public function checkCreditLine($params)
    {
        $makeOrderForId = Session::get("makeOrderFor");

        if (empty($makeOrderForId) && ResellerHelper::isReseller($params['clientId'])) {
            $reseller = ResellerHelper::getLogged();
            if (!$reseller->settings->admin->resellerInvoice) {
                return null;
            }
            $selectedGateway = $params['paymentmethod'];
            $service = new ResellerCreditLineService();
            $clientId = $params['clientId'];
            $dumpInvoice = new WhmcsInvoiceModel();
        } else {
            $reseller = ResellerHelper::getCurrent();
            $selectedGateway = $reseller->settings->admin->resellerInvoice ? Session::get("rcChoosenGateway") : $params['paymentmethod'];
            $service = new ResellerClientCreditLineService();

            $clientId = $makeOrderForId ?: $params['clientId'];
            if ($reseller->settings->admin->resellerInvoice) {
                $dumpInvoice = new RcInvoiceModel();
                $dumpInvoice->reseller_id = $reseller->id;
            } else {
                $dumpInvoice = new WhmcsInvoiceModel();
            }
        }

        if (!$reseller->exists) {
            return null;
        }

        if (!function_exists("calcCartTotals")) {
            require ROOTDIR . "/includes/orderfunctions.php";
        }

        $client = \WHMCS\User\Client::find($clientId);

        if ($params['applycredit'] && $client->credit > 0) {
            return null;
        }

        $cart = calcCartTotals($client, false, false);

        $dumpInvoice->userid = $clientId;
        $dumpInvoice->total = $cart['rawtotal'];
        $dumpInvoice->paymentmethod = $selectedGateway;

        try {
            $service->checkIsAddCreditPossible($dumpInvoice);
        } catch (NoAvailableCreditLineException | WrongPaymentMethodException $e) {
            return null;
        } catch (ClientLimitExceedException | ResellerLimitExceedException $e) {
            return Lang::T('creditLinesErrorMessages', $e->getMessage());
        }
    }
}