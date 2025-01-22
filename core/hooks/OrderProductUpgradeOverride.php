<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Whmcs\Products\Products\Product;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\core\Session;
use MGModule\ResellersCenter\core\Redirect;
use MGModule\ResellersCenter\Core\Whmcs\Services\Hosting\Hosting;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings\EndClientConsolidatedInvoices;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\libs\CreditLine\Exceptions\ClientLimitExceedException;
use MGModule\ResellersCenter\libs\CreditLine\Exceptions\NoAvailableCreditLineException;
use MGModule\ResellersCenter\libs\CreditLine\Exceptions\ResellerLimitExceedException;
use MGModule\ResellersCenter\libs\CreditLine\Exceptions\WrongPaymentMethodException;
use MGModule\ResellersCenter\libs\CreditLine\Services\SubServices\ResellerClientCreditLineService;
use MGModule\ResellersCenter\libs\CreditLine\Services\SubServices\ResellerCreditLineService;
use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\models\Invoice as RcInvoiceModel;
use MGModule\ResellersCenter\models\whmcs\Invoice as WhmcsInvoiceModel;

/**
 * Description of OrderProductPricingOverride
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class OrderProductUpgradeOverride
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     *
     * @var array
     */
    public $functions;

    public static $params;

    /**
     * Assign anonymous function
     */
    public function __construct()
    {
        $this->functions[PHP_INT_MAX - 20] = function($params) {
            self::$params = $this->checkCreditLinesForReseller($params);
        };

        $this->functions[PHP_INT_MAX - 10] = function($params) {
            self::$params = $this->manageDeferredGateway(self::$params);
        };

        $this->functions[PHP_INT_MAX] = function($params) {
            return $this->setUpgradePriceInCart(self::$params);
        };
    }

    public $billingcycles = array("monthly" => 30, "quarterly" => 90, "semiannually" => 182, "annually" => 365, "biennially" => 730, "triennially" => 1096);


    public function checkCreditLinesForReseller($params)
    {
        if (Session::get("makeOrderFor")) {
            return $params;
        }

        $reseller = Reseller::getLogged();
        if (!$reseller->exists || !$reseller->settings->admin->resellerInvoice) {
            return $params;
        }

        $dumpInvoice = new WhmcsInvoiceModel();

        $hosting = new Hosting(Request::get("id"));
        $creditLineService = new ResellerCreditLineService();

        $dumpInvoice->userid = $hosting->userid;
        $dumpInvoice->total = $params['price'];
        $dumpInvoice->paymentmethod = Request::get('paymentmethod');

        try {
            $creditLineService->checkIsAddCreditPossible($dumpInvoice);
        } catch (NoAvailableCreditLineException | WrongPaymentMethodException $e) {
            return null;
        } catch (ClientLimitExceedException $e) {
            $this->redirectWithMessage($e->getMessage());
        }

        return $params;
    }

    public function manageDeferredGateway($params)
    {
        $consolidatedEnable = SettingsManager::isConsolidatedEnableForCurrentReseller(null);
        if ($consolidatedEnable) {
            global $smarty;
            $smarty->assign('rcForceAllowGatewaySelection', true);
        }
        return $params;

    }
    /**
     * Set correct price for product upgrade in cart
     * 
     * @param type $params
     * @return type
     */
    public function setUpgradePriceInCart($params)
    {
        $reseller = Reseller::getCurrent();
        if (!$reseller->exists) {
            return $params;
        }

        $newProduct = new Product($params["newproductid"], $reseller);
        $newBillingCycle = $params["newproductbillingcycle"];

        $hosting = new Hosting(Request::get("id"));
        $upgrade = $hosting->getUpgrade($newProduct, $newBillingCycle);

        global $smarty;

        $enableStartNewBillingInput = SettingsManager::getSettingFromReseller($reseller, EndClientConsolidatedInvoices::NAME) == 'on';
        $startNewBillingPeriod = $_REQUEST['startNewPeriod'] && $enableStartNewBillingInput;

        if ($smarty) {
            $smarty->assign('enableStartNewPeriod', $enableStartNewBillingInput);
            $smarty->assign('startNewPeriodLabel', Lang::absoluteT('consolidatedInvoices', 'startNewPeriodLabel'));

            if ($startNewBillingPeriod) {
                $smarty->assign('startNewPeriod', true);
                $upgrade->setStartNewPeriodFlag();
            } else {
                $smarty->assign('startNewPeriod', false);
            }
        }

        $newPrice = $upgrade->getPrice();

        $this->checkCreditLinesForResellersClient($reseller, $newPrice);

        if (Request::get("step") == 3) {

            //Set max price to make refund or create invoice to reseller
            $newPrice = $upgrade->getAdminPrice();
            if ($newPrice <= 0) {
                Session::set("RC_AbortAutoUpgrade", true);
            }

            Session::set("RC_OldBillingCycle", $hosting->billingcycle);
            Session::set("RC_UpgradeUid", Session::get("uid"));
            Session::set("RC_UpgradeStartNewPeriod", $startNewBillingPeriod);
            Session::set("uid", $reseller->client->id);
        } elseif ($reseller->client->credit < ($newPrice * (-1))) {
            //Check if reseller has credits to make refund for client
            global $id;
            Session::set("upgradeResellerError", 1);
            Redirect::toPageWithQuery("upgrade.php", ["error" => "notEnoughCredits", "id" => $id]);
        }

        return ["price" => $newPrice];
    }

    private function checkCreditLinesForResellersClient($reseller, $price)
    {
        if ($reseller->settings->admin->resellerInvoice) {
            $dumpInvoice = new RcInvoiceModel();
            $dumpInvoice->reseller_id = $reseller->id;
        } else {
            $dumpInvoice = new WhmcsInvoiceModel();
        }

        $hosting = new Hosting(Request::get("id"));

        $makeOrderForId = Session::get("makeOrderFor");
        $clientId = $makeOrderForId ?: $hosting->userid;
        $creditLineService = new ResellerClientCreditLineService();

        $dumpInvoice->userid = $clientId;
        $dumpInvoice->total = $price;
        $dumpInvoice->paymentmethod = Request::get('paymentmethod');

        try {
            $creditLineService->checkIsAddCreditPossible($dumpInvoice);
        } catch (NoAvailableCreditLineException | WrongPaymentMethodException $e) {
            return null;
        } catch (ClientLimitExceedException | ResellerLimitExceedException $e) {
            $this->redirectWithMessage($e->getMessage());
        }
    }

    private function redirectWithMessage($message)
    {
        global $smarty;
        $messageTranslated = Lang::T('creditLinesErrorMessages', $message);
        $smarty->assign('rcUpgradeErrorMessage', $messageTranslated);
        $smarty->assign('rcUpgradeErrorFlag', true);

        if (Request::get("step") == 3) {
            $data = array_merge($_REQUEST, ["step" => 2]);
            unset($data['configoption']);
            Redirect::toPageWithQuery("upgrade.php", $data);
        }
    }
}