<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\core\Server;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings\EnableConsolidatedInvoices;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\repository\whmcs\Products;

class CheckoutSubmitGateway
{
    public $functions;

    public function __construct()
    {
        $this->functions = [];
    }
    /**
     * Due to WHMCS8 new order checkout payment gateway validation we need to bypass it
     * This hook is for saving choosen gateway, then replacing it with any WHMCS gateway for proper validation
     * 'Hook' is implemented below
     */
}

if((basename(Server::get("SCRIPT_NAME")) == 'cart.php')
    && (Request::get("a") == 'checkout')
    && (Request::get("submit") == 'true' || Request::get("submit") == '1')
)
{
    if(Whmcs::isVersion('8.0')
        && (Reseller::isMakingOrderForClient() || Session::get('loggedAsClient') || Session::get('branded')))
    {
        if(Reseller::getCurrent()->settings->admin->resellerInvoice)
        {
            /* Saving choosen gateway in session */
            Session::set('rcChoosenGateway', Request::get('paymentmethod'));
            
            $repo = new Products();
            $excludeGateways = [];
            foreach($_SESSION['cart']['products'] as $product)
            {
                $disabledGateways = $repo->getProductDisabledPaymentGateways($product['pid']);
                $excludeGateways = array_merge($disabledGateways, $excludeGateways);
            }

            /* Replacing with first enabled WHMCS gateway */
            $whmcsFirstEnabledGateway = Whmcs::getFirstAvailableGateway($excludeGateways)['sysname'] ?: 'banktransfer';
            Request::set('paymentmethod', $whmcsFirstEnabledGateway);
        }
        /* Clearing auto credit payment in WHMCS 8 if credit payment options is disabled */
        $reseller = Reseller::getCurrent();

        $consolidatedEnable = SettingsManager::isConsolidatedEnableForCurrentReseller($reseller);

        if ($consolidatedEnable || ($reseller->settings->admin->resellerInvoice && !$reseller->settings->admin->allowCreditPayment)) {
            Request::clear('applycredit');
        }

        \App::replace_input(Request::get());
    } else {
        //check for reseller directly
        $reseller = Reseller::getLogged();
        if ($reseller->exists &&
            $reseller->settings->admin->resellerInvoice &&
            SettingsManager::getSettingFromReseller($reseller, EnableConsolidatedInvoices::NAME) == 'on') {
            Request::clear('applycredit');
            \App::replace_input_vars(['applycredit'=>false]);
        }
    }
}