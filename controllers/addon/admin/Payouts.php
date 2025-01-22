<?php
namespace MGModule\ResellersCenter\Controllers\Addon\Admin;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;

use MGModule\ResellersCenter\repository\Resellers as ResellersRepo;
use MGModule\ResellersCenter\repository\ResellersProfits;
use MGModule\ResellersCenter\repository\whmcs\Currencies;
use MGModule\ResellersCenter\repository\whmcs\Configuration;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\core\datatable\DatatableDecorator as Datatable;
use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;

use MGModule\ResellersCenter\core\Request;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\Payout;
use PayPal\Api\PayoutSenderBatchHeader;
use PayPal\Api\Currency;
use PayPal\Api\PayoutItem;

/**
 * Payouts controllers
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Payouts extends AbstractController
{            
    public function indexHTML()
    {
        $repo = new Configuration();
        
        return array(
            'tpl'   => 'base',
            'vars' => array(
                "paypal" => array(
                    "sandbox" => $repo->getSetting("RCPayPalPayoutSandBox"),
                    "appKey" => $repo->getSetting("RCPayPalAppKey"),
                    "secret" => $repo->getSetting("RCPayPalSecret"),
                )
            )
        );
    }

    /**
     * Get resellers for select2 field.
     * Load only reseller that have thier paypal email filled
     * and admin has enabled PayPal Auto Transfer for them.
     * 
     * @return type
     */
    public function getResellersJSON()
    {
        $type = Request::get("type");
        $search = Request::get("term");
        
        $repo = new ResellersRepo();
        $model = $repo->getModel();
        $resellers = $model->withFilter($search)->get();

        $currencies = new Currencies();
        $currency = $currencies->getDefault();
        
        $result = array();
        foreach($resellers as $model) 
        {
            $reseller = new Reseller($model->id);
            
            //Static filters
            if($type != 'all' && (!$reseller->settings->private->paypalEmail || !$reseller->settings->admin->paypalAutoTransfer))
            {
                continue;
            }
            
            $payout = 0;
            foreach($reseller->profits as $profit) 
            {
                if(! $profit->collected) 
                {
                    $payout += $profit->amount;
                }
            }
            
            //Skip resellers that has nothing to payout - all profits are collected or no profits extists
            if($payout == 0) 
            {
                continue;
            }
            
            $amount = $currency->prefix . $payout . $currency->suffix;
            $result[] = array(
                "id" => $reseller->id,
                "name" => $reseller->client->firstname . " " . $reseller->client->lastname. " - " . $amount,
                "groupname" => $reseller->group->name
            );
        }
        
        return $result;
    }
    
    /**
     * Calculate total profit of selected resellers.
     * This function will calculate only profit that has not been collected yet.
     * 
     * @return type
     */
    public function calculateTotalResellersProfitJSON()
    {
        $resellersids = Request::get("resellersids");
        $repo = new ResellersRepo();
        $resellers = $repo->find($resellersids);

        $result = 0;
        foreach($resellers as $reseller)
        {
            foreach($reseller->profits as $profit) 
            {
                if(! $profit->collected) {
                    $result += $profit->amount;
                }
            }
        }
        
        $currencies = new Currencies();
        $currency = $currencies->getDefault();
        
        return $currency->prefix . $result . $currency->suffix; 
    }
    
    /**
     * Mannualy set payout as collected.
     * Used when Admin make payouts using different method then paypal
     * or use paypal outside of Resellers Center.
     * 
     * @return type
     */
    public function setAsCollectedJSON()
    {
        $profitid = Request::get("profitid");
        
        $repo = new ResellersProfits();
        $repo->setAsCollected($profitid);

        EventManager::call("profitSetAsCollected", $profitid);
        return array("success" => Lang::T('collected','success'));
    }
    
    /**
     * Make PayPal payment for single reseller profit record
     * If there will be no errors in paypal API then profit record will
     * be set as collected
     * 
     * @return type
     */
    public function makeSinglePayPalPaymentJSON()
    {
        $profitid = Request::get("profitid");
        
        $repo = new ResellersProfits();
        $profit = $repo->find($profitid);
        
        //Make API call
        $result = $this->makePayPalPayment(array($profit->reseller_id => array($profit)));
        if(isset($result["error"])) {
            return array("error" => $result["error"]);
        }
        
        //Mark profit as collected
        $repo->setAsCollected($profitid);
        
        EventManager::call("singlePayPalPaymentMade", $profitid);
        return array("success" => Lang::T('paypal','payment','success'));
    }
   
    /**
     * Make credit payment for reseller
     * 
     * @return type
     */
    public function makeSingleCreditsPaymentJSON()
    {
        $profitid = Request::get("profitid");
        
        $repo = new ResellersProfits();
        $profit = $repo->find($profitid);
        
        $result = WhmcsAPI::request("addcredit", array(
            "clientid" => $profit->reseller->client_id,
            "description" => "Payout for sales for service #{$profit->service_id} (invoice item #{$profit->invoiceitem_id})",
            "amount" => $profit->amount
        ));
            
        if($result["result"] != 'success') {
            return array("success" => $result["message"]);
        }
        
        //Mark profit as collected
        $repo->setAsCollected($profitid);
        
        EventManager::call("singleCreditPaymentMade", $profitid);
        return array("success" => Lang::T('credit','payment','success'));
    }
    
    /**
     * Make Paypal payment for one or more resellers.
     * All profit records that belongs to selected resellers will be 
     * set as collected if there will not be any API errors.
     * 
     * @return type
     */
    public function makeMassPayPalPaymentJSON()
    {
        $resellersids = Request::get("resellersids");
        if(empty($resellersids)) {
            return array("error" => Lang::T('paypal','error','emptyresellers'));
        }
        
        $repo = new ResellersRepo();
        $resellers = $repo->find($resellersids);
        
        //Summarize profits per reseller - it will be cheaper for admin
        $profits = array();
        foreach($resellers as $reseller) 
        {
            foreach($reseller->profits as $profit)
            {
                if(!$profit->collected){
                    $profits[$reseller->id][] = $profit;
                }
            }
        }
        
        $result = $this->makePayPalPayment($profits);
        if(isset($result["error"])) {
            return array("error" => $result["error"]);
        }

        //Mark profits as collected
        $profitsRepo = new ResellersProfits();
        foreach($resellers as $reseller) 
        {
            foreach($reseller->profits as $profit) {
                $profitsRepo->setAsCollected($profit->id);
            }
        }
        
        EventManager::call("massPayPalPaymentMade", $resellersids);
        return array("success" => Lang::T('paypal','masspayment','success'));
    }
    
    /**
     * Make Credit payment for one or more resellers.
     * All profit records that belongs to selected resellers will be 
     * set as collected if there will not be any API errors.
     * 
     * @return type
     */
    public function makeMassCreditPaymentJSON()
    {
        $resellersids = Request::get("resellersids");
        if(empty($resellersids)) {
            return array("error" => Lang::T('paypal','error','emptyresellers'));
        }
        
        $repo = new ResellersRepo();
        $resellers = $repo->find($resellersids);
        
        $profitsRepo = new ResellersProfits();
        foreach($resellers as $reseller) 
        {
            $amount = 0;
            foreach($reseller->profits as $profit) 
            {
                if(!$profit->collected)
                {
                    $amount += $profit->amount;
                }                  
            }
            
            $result = WhmcsAPI::request("addcredit", array(
                "clientid" => $profit->reseller->client_id,
                "description" => "Mass payout for sales",
                "amount" => $amount
            ));

            if($result["result"] != 'success') {
                return array("success" => $result["message"]);
            }
            
            //Mark profit as collected
            foreach($reseller->profits as $profit) {
                $profitsRepo->setAsCollected($profit->id);   
            }
        }
        
        EventManager::call("massCreditsPaymentMade", $resellersids);
        return array("success" => Lang::T('paypal','masspayment','success'));
    }
    
    public function saveConfigrationJSON()
    {
        $settings = Request::get("settings");
        $settings["RCPayPalPayoutSandBox"] = $settings["RCPayPalPayoutSandBox"] ?: "";
        
        $repo = new Configuration();
        foreach($settings as $setting => $value)
        {
            $repo->saveSetting($setting, $value);
        }
        
        EventManager::call("paypalPayoutsUpdated", $settings);
        return array("success" => Lang::T('paypal','configuration','save','success'));
    }

    public function getPayoutsTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();

        $profits = new ResellersProfits();
        $result = $profits->getDataForTable($dtRequest);

        $format = array(
            "firstname" => array("link" => array("resellerid", "reseller")),
            "lastname"  => array("link" => array("resellerid", "reseller")),
            "invoice"   => array("link" => array("invoice_id", "invoice")),
            "status"    => array("lang" => array('status'),
                                 "class" => array(array(1, "label label-success"), array(0, "label label-danger")))
        );
        
        $buttons = array(
            array(
                "type" => "only-icon", 
                "class" => "payByPaypal btn-info", 
                "data" => array("profitid" => "id"), 
                "icon" => "fa fa-paypal", 
                "if" => array(array("paypaltransfer", "==", "on"), array("status", "==", "0")),
                "tooltip" => Lang::T("table", "paypalInfo")
            ),
            array(
                "type" => "only-icon", 
                "class" => "payByCredits btn-default",
                "data" => array("profitid" => "id"), 
                "image" => "images/icons/income.png", 
                "if" => array("status", "0"),
                "tooltip" => Lang::T("table", "creditsInfo")
            ),
            array(
                "type" => "only-icon", 
                "class" => "setAsCollected btn-success", 
                "data" => array("profitid" => "id"), 
                "icon" => "fa fa-check", 
                "if" => array("status", "0"),
                "tooltip" => Lang::T("table", "acceptInfo")
            ),
        );
        
        $datatable = new Datatable($format, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }
    
    /**
     * Make payouts for reseller.
     * This will work up to 500 reseller each call - should be enough...
     * 
     * @pram type $profits
     * @return type
     */
    private function makePayPalPayment($resellersProfit)
    {
        $summarized = array();
        foreach($resellersProfit as $profits)
        {
            foreach($profits as $profit) {
                $email = $profit->reseller->settings["private"]["paypalEmail"];
                $summarized[$email] += $profit->amount;
            }
        }
                
        $currencies = new Currencies();
        $currency = $currencies->getDefault();
        
        $config = new Configuration();
        $appKey = $config->getSetting("RCPayPalAppKey");
        $secret = $config->getSetting("RCPayPalSecret");
        $mode   = $config->getSetting("RCPayPalPayoutSandBox") ? "SANDBOX" : "LIVE";
        
        if(empty($appKey) || empty($secret)){
            return array("error" => Lang::T('paypal','configuration', 'error'));
        }
        
        try 
        {
            $this->makePayPalApiCall($appKey, $secret, $summarized, $currency, $mode);
        }
        catch (Exception $ex) 
        {
            return array("error" => $ex->getMessage());
        }
        
        return array("success" => true);
    }
    
    /**
     * 
     * 
     * @param type $appKey - this is clientID from Paypal Application panel
     * @param type $secret
     * @param type $items
     * @param type $currency
     */
    private function makePayPalApiCall($appKey, $secret, $items, $currency, $mode = "LIVE")
    {
        $credential = new OAuthTokenCredential($appKey, $secret);
        $apiContext = new ApiContext($credential);
        $apiContext->setConfig(["mode" => $mode]);
        
        $senderBatchHeader = new PayoutSenderBatchHeader();
        $senderBatchHeader->setSenderBatchId(uniqid())->setEmailSubject("Reseller Payout");
        
        $payout = new Payout();
        $payout->setSenderBatchHeader($senderBatchHeader);
        
        foreach($items as $email => $amount)
        {
            $currency = array("value" => $amount, "currency" => trim($currency->suffix));
            $value = new Currency(json_encode($currency));
            $item = new PayoutItem();
            $item->setRecipientType('EMAIL')->setReceiver($email)->setSenderItemId("payout" . uniqid())->setAmount($value);

            $payout->addItem($item);
        }
        
        $payout->create(null, $apiContext);
    }
}
