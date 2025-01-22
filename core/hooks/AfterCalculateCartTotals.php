<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller as ResellerObj;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\gateways\DeferredPayments\DeferredPayments;
use MGModule\ResellersCenter\libs\CreditLine\Services\SubServices\ResellerCreditLineService;
use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\repository\Resellers as ResellersRepo;

class AfterCalculateCartTotals
{
    public $functions;

    /**
     * Assign anonymous function
     */
    public function __construct()
    {
        $this->functions[-1000] = function($params) {
            return $this->checkCreditLinesAdminArea($params);
        };
    }

    public function checkCreditLinesAdminArea($params)
    {
        if (defined('ADMINAREA') &&
            defined('SHOPPING_CART') &&
            Request::get("calconly") &&
            $params['rawtotal'] > 0)
        {
            $paymentMethod = Request::get('paymentmethod');

            if ($paymentMethod != DeferredPayments::SYS_NAME) {
                return $params;
            }
            $userId = Request::get('userid');
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

            if (!($creditLine->limit - $creditLine->usage >= $params['rawtotal'])) {
                $message = Lang::T('creditLinesErrorMessages', 'notEnoughCredits');
                echo "<div class='alert alert-danger'>{$message}</div>";
                global $aInt;
                $content = ob_get_contents();
                ob_end_clean();
                $aInt->jsonResponse(["body" => $content]);
            }
        }

        return $params;
    }

}