<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller as ResellerObj;
use MGModule\ResellersCenter\core\Session;
use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\gateways\DeferredPayments\DeferredPayments;
use MGModule\ResellersCenter\libs\CreditLine\Services\SubServices\ResellerCreditLineService;
use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\repository\Resellers as ResellersRepo;

/**
 * Description of PreShoppingCartCheckout
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class PreShoppingCartCheckout 
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     * 
     * @var array 
     */
    public $functions;
    
    /**
     * Container for hook params
     * 
     * @var type 
     */
    public static $params;
    
    /**
     * Assign anonymous function
     */
    public function __construct() 
    {
        $this->functions[30] = function($params) {
            self::$params = $this->checkCreditLinesAdminArea($params);
        };

        $this->functions[40] = function($params) {
            self::$params = $this->checkIfAnyGatewayIsEnabled($params);
        };

        $this->functions[50] = function($params) {
            self::$params = $this->swapUserId($params);
        };
        
        $this->functions[60] = function($params) {
           return $this->disablePromotion($params);
        };
    }

    public function checkCreditLinesAdminArea($params)
    {
        if (!defined('ADMINAREA') || !defined('SHOPPING_CART')){
            return $params;
        }

        $paymentMethod = Request::get('paymentmethod');

        if ($paymentMethod != DeferredPayments::SYS_NAME) {
            return $params;
        }

        $userId = Request::get('userid');
        $submitOrder = Request::get('submitorder');

        if (!$userId || !$submitOrder) {
            return $params;
        }

        if (!function_exists("calcCartTotals")) {
            require ROOTDIR . "/includes/orderfunctions.php";
        }

        $client = \WHMCS\User\Client::find($userId);
        $cart = calcCartTotals($client, false, false);

        $repo = new ResellersRepo();
        $resellerModel = $repo->getResellerByClientId($userId);
        $reseller = new ResellerObj($resellerModel);

        if (!$reseller->settings->admin->resellerInvoice) {
            return $params;
        }

        $creditLineService = new ResellerCreditLineService();
        $creditLine = $creditLineService->getEnableCreditLine($userId);

        if (!(bool)$creditLine->limit) {
            return $params;
        }

        if (!($creditLine->limit - $creditLine->usage >= $cart['rawtotal'])) {
            $message = Lang::T('creditLinesErrorMessages', 'notEnoughCredits');
            global $aInt;
            $aInt->gracefulExit($message);
        }

        return $params;
    }
    
    /**
     * Check if there is any gateway enabled in reseller store
     * 
     * @param type $params
     * @return type
     */
    public function checkIfAnyGatewayIsEnabled($params)
    {
        $reseller = ResellerHelper::getCurrent();
        if($reseller->exists && $reseller->settings->admin->resellerInvoice)
        {
            $isEnabled = false;
            
            $gateways = Helper::getCustomGateways($reseller->id);
            foreach($gateways as $gateway)
            {
                if($gateway->enabled)
                {
                    $isEnabled = true;
                    break;
                }
            }
                
            if(!$isEnabled && !$reseller->settings->admin->disableEndClientInvoices)
            {
                die("Unexpected payment method value. Exiting.");
            }
        }
        
        return $params;
    }

    /**
     * This function is used when Reseller is making order for his client.
     * Just before any database query it changes userid in SESSION.
     *
     * @param type $params
     * @return type
     */
    public function swapUserId($params)
    {
        if(Reseller::isMakingOrderForClient())
        {
            $resellerid =  Session::get("resellerid");
            if(empty($resellerid)) {
                $resellerid = Session::get("uid");
                Session::set("resellerid", $resellerid);
            }

            $clientid = Session::get("makeOrderFor");
            Session::set("uid", $clientid);
        }

        return $params;
    }
    
    /**
     * Disable promotion in reseller store
     * 
     * @param type $params
     * @return type
     */
    public function disablePromotion($params)
    {
        $reseller = ResellerHelper::getByCurrentURL();
        if($reseller->exists && !$reseller->settings->admin->promotions) 
        {
            unset($_SESSION["cart"]["promo"]);
        }
        
        return $params;
    }

}