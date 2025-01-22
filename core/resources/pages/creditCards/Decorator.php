<?php

namespace MGModule\ResellersCenter\Core\Resources\Pages\CreditCards;

use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\models\whmcs\Client as ClientModel;
use MGModule\ResellersCenter\core\resources\gateways\Factory as PaymentGatewayFactory;

/**
 * Description of Decorator
 *
 * @author Paweł Złamaniec
 */
class Decorator 
{
    public function getCAPageView(\MGModule\ResellersCenter\Core\Resources\Invoices\Invoice $invoice, $vars = array())
    {
        global $whmcs;
        
        //Load WHMCS page
        $ca = new \WHMCS\ClientArea;
        $ca->setPageTitle($whmcs->get_lang('creditcard'));
        $ca->addToBreadCrumb('index.php', $whmcs->get_lang('globalsystemname'));
        $ca->addToBreadCrumb('rccreditcard.php', $whmcs->get_lang('creditcard'));
        $ca->initPage();

        $params = array_merge($vars, $this->getViewParams($invoice, $vars));
        foreach($params as $key => $value) {
            $ca->assign($key, $value);
        }

        $ca->setTemplate('rccreditcard');
        return $ca->output();
    }
    
    private function getViewParams($invoice, $vars = [])
    {
        global $CONFIG;
        
        $client = new Client($invoice->userid);

        $ccDetails = $client->getCCDetails();
        $clientDetails = $client->toArray();
        $existingClientCards = [];
        $billingContactId = null;
        $payMethodId = $vars["ccinfo"];
        $countryObject = new \WHMCS\Utility\Country();

        $clientModel = ClientModel::findOrFail($invoice->userid);
        $gateway = new \WHMCS\Module\Gateway();
        $gatewayName = strtolower($invoice->paymentmethod);
        $gateway->load($gatewayName);

        $gatewayCards = $clientModel->payMethods->creditCards()->validateGateways()->sortByExpiryDate()
            ->filter(function (\WHMCS\Payment\Contracts\PayMethodInterface $payMethod) use ($gateway) {
                if ($payMethod->getType() === \WHMCS\Payment\Contracts\PayMethodTypeInterface::TYPE_CREDITCARD_LOCAL &&
                    !in_array($gateway->getWorkflowType(), [\WHMCS\Module\Gateway::WORKFLOW_ASSISTED, \WHMCS\Module\Gateway::WORKFLOW_REMOTE])) {
                    return true;
                }

                $payMethodGateway = $payMethod->getGateway();
                return $payMethodGateway && $payMethodGateway->getLoadedModule() === $gateway->getLoadedModule();
            });

        if (!function_exists('getPayMethodCardDetails')) {
            require_once ROOTDIR.DS.'includes'.DS.'ccfunctions.php';
        }

        if (!function_exists('getCountriesDropDown')) {
            require_once ROOTDIR.DS.'includes'.DS.'clientfunctions.php';
        }

        $country = $client->getCountryName();

        foreach ($gatewayCards as $key => $creditCardMethod) {
            if (is_null($lowestOrder) || $creditCardMethod->order_preference < $lowestOrder) {
                $lowestOrder = $creditCardMethod->order_preference;
                $defaultCardKey = $key;
            }
            $existingClientCards[$key] = getPayMethodCardDetails($creditCardMethod);
        }

        $existingCard = ["cardtype" => NULL,
            "cardlastfour" => NULL,
            "cardnum" => \Lang::trans("nocarddetails"),
            "fullcardnum" => NULL,
            "expdate" => "",
            "startdate" => "",
            "issuenumber" => NULL,
            "gatewayid" => NULL,
            "billingcontactid" => NULL];

        if (!empty($existingClientCards)) {
            $existingCard = $existingClientCards[$defaultCardKey];
            if (!$payMethodId) {
                $payMethodId = $existingCard["paymethodid"];
                $billingContactId = $existingCard["billingcontactid"];
            }
        }

        $hasExistingCard = 0 < strlen($existingCard["cardlastfour"]);

        $params = [
            "invoiceid" => $invoice->id,
            "invoicenum" => $invoice->invoicenum,
            "invoice" => $invoice->toArray(),
            "invoiceitems" => $invoice->items->toArray(),

            "acceptedcctypes" => Helper::getAcceptedCCTypes(),
            "existingCards" => $existingClientCards ?: [],

            "ccnumber" => $ccDetails->cardnum,
            "ccstartmonth" => $ccDetails->startdate,
            "ccexpirymonth" => $ccDetails->expdate,
            "ccissuenum" => $ccDetails->issuenumber,

            "countryname" => $country,
            "countriesdropdown" => getCountriesDropDown($clientDetails["country"]),
            "countries" => $countryObject->getCountryNameArray(),

            "showccissuestart" => $CONFIG["ShowCCIssueStart"],
            
            "months" => $this->getCCDateMonths(),
            "startyears" => $this->getCCStartDateYears(),
            "expiryyears" => $this->getCCExpiryDateYears(),
            "cardOrBank" => "card",
            "cardOnFile" => $hasExistingCard,
            "addingNewCard" => $payMethodId === "new" || !$hasExistingCard,
            "addingNew" => $payMethodId === "new" || !$hasExistingCard,
            "payMethodId" => $payMethodId,
            "billingContact" => $billingContactId,
            "cardtype" => $existingCard["cardtype"],
            "cardnum" => $existingCard["cardlastfour"],
            "existingCardType" => $existingCard["cardtype"],
            "existingCardLastFour" => $existingCard["cardlastfour"],
            "existingCardExpiryDate" => $existingCard["expdate"],
            "existingCardStartDate" => $existingCard["startdate"],
            "existingCardIssueNum" => $existingCard["issuenumber"],
            "submitLocation" => "rccreditcard.php"
        ];
        $params = array_merge($params, $clientDetails);
        $params["invoice"]["taxname"] = $client->tax->name;
        $params["invoice"]["tax2name"] = $client->tax2->name;
        $params["invoice"]["amountpaid"] = formatCurrency($invoice->amountpaid);
        $params["balance"] = formatCurrency($invoice->total - $invoice->amountpaid);
        $params["gateway"] = PaymentGatewayFactory::get($invoice->reseller->id, $invoice->paymentmethod);

        if ($gateway->functionExists("credit_card_input")) {
            $params["credit_card_input"] = $gateway->call("credit_card_input", $params);
        }

        return $params;
    }
    
    public function getCCDateMonths() 
    {
        $months = array();
        $i = 1;

        while ($i <= 12) 
        {
            $months[] = str_pad($i, 2, "0", STR_PAD_LEFT);
            ++$i;
        }

        return $months;
    }

    public function getCCStartDateYears() 
    {
        $startyears = array();
        $i = date("Y") - 12;

        while ($i <= date("Y")) 
        {
            $startyears[] = $i;
            ++$i;
        }

        return $startyears;
    }

    public function getCCExpiryDateYears() 
    {
        $i = date("Y");
        $expiryyears = array();

        while ($i <= date("Y") + 12) 
        {
            $expiryyears[] = $i;
            ++$i;
        }

        return $expiryyears;
    }
}
