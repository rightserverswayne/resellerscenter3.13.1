<?php
namespace MGModule\ResellersCenter\gateways\Stripe\Components\Submodule\Stripe3;
use MGModule\ResellersCenter\gateways\Stripe\Components\Helpers\TokenHelper;
use MGModule\ResellersCenter\gateways\Stripe\Components\Interfaces\AbstractStripeService;
use MGModule\ResellersCenter\gateways\Stripe\Components\Models\StripeRequest;
use MGModule\ResellersCenter\repository\whmcs\CreditCards;
use MGModule\ResellersCenter\core\Request;


/**
 *
 * Created by PhpStorm.
 * User: Tomasz Bielecki ( tomasz.bi@modulesgarden.com )
 * Date: 04.03.20
 * Time: 08:04
 * Class Services
 *
 * @property StripeRequest $model
 */
class StripeServices extends AbstractStripeService
{

    /**
     * @var
     */
    protected $paymentMethodId;
    protected $customerId;
    /**
     * @var
     */
    protected $client;

    /**
     *
     */
    protected function loadClient()
    {
        $sessionUser = \WHMCS\Session::get("uid");
        $this->client = \WHMCS\User\Client::find($sessionUser);
    }

    /**
     * @param $paymentMethodId
     * @return $this
     */
    public function setPaymentMethodId($paymentMethodId)
    {
        $this->paymentMethodId = $paymentMethodId;

        return $this;
    }
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

    public function getPaymentMethodId()
    {
        return $this->paymentMethodId;
    }

    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function capture()
    {
        $this->loadClient();

        $intent = $this->getIntentByToken();
        $this->setPaymentMethodId($intent->payment_method);
        $this->setCustomerId($intent->customer);
        $this->executeCapture($intent);
        $result = $this->finishTransaction($intent->charges->data[0]);

        return $result;
    }

    /**
     * @param $intent
     * @return mixed
     * @throws \Exception
     */
    protected function executeCapture(&$intent)
    {
        if ($intent->status == "requires_capture") {
            $intent->capture();
        }

        if ($intent->status != "succeeded") {
            $error = $intent->last_payment_error;
            if (!$error) {
                $error = "Cardholder Action Required";
            }
            throw new \Exception($error);
        }

        return $intent;
    }

    protected function finishTransaction($charge)
    {
        $transaction = \Stripe\BalanceTransaction::retrieve($charge->balance_transaction);

        $transactionFeeCurrency = \WHMCS\Database\Capsule::table("tblcurrencies")
            ->where("code", "=", strtoupper($transaction->fee_details[0]->currency))
            ->first(array("id"));

        $transactionId = $transaction->id;
        $transactionFee = 0;
        if ($transactionFeeCurrency) {
            $transactionFee = convertCurrency($transaction->fee / 100, $transactionFeeCurrency->id, $this->client->currencyId);
        }

        return array(
            "status" =>
                "success",
            "transid" => $transactionId,
            "amount" => $this->model->getAmount(),
            "fee" => $transactionFee,
            "rawdata" =>
                array(
                    "charge" => $charge->jsonSerialize(),
                    "transaction" => $transaction->jsonSerialize()
                )
        );

    }

    protected function getIntentByToken()
    {
        $client = $this->client;
        $paymentMethodId = $this->model->getStripeToken() ?: $this->model->getGatewayid();

        if (empty($paymentMethodId)) {
            $gateway = new \WHMCS\Module\Gateway();
            $gateway->load("stripe");
            $paymentMethodId = stripe_findFirstCustomerToken($this->client);
            $paymentMethodId = $paymentMethodId['method'];
        }

        if($paymentMethodId)
        {
            // if is payment intent (pi) token, get PI object
            if (substr($paymentMethodId, 0, 2) == "pi")
            {
                $intent = \Stripe\PaymentIntent::retrieve($paymentMethodId);
                return $intent;
            }
            $method = \Stripe\PaymentMethod::retrieve($paymentMethodId);
        }

        /**
         * check if customer exist
         */
        if ($method->customer) {
            $stripeCustomer = \Stripe\Customer::retrieve($method->customer);
        } elseif($this->model->getCustType() == 'existing' && $this->model->getCcinfo() == 'new')
        {
            // if existing customer is adding new card
            $clientWhmcs         = \WHMCS\User\Client::find($client->id);
            if($clientWhmcs->payMethods)
            {
                // go through his payMethods ..
                foreach ($clientWhmcs->payMethods as $payMethod)
                {
                    // ..and look for the stripe card
                    if ($payMethod->gateway_name == $this->model->getPaymentMethod())
                    {
                        $payment = $payMethod->payment;
                        $data = json_decode($payment->getRemoteToken(), true);

                        // ..with customer id
                        if ($data['customer'])
                        {
                            $stripeCustomer   =   \Stripe\Customer::retrieve($data['customer']);
                            break;
                        }
                    }
                }
            }
        } elseif(is_numeric($this->model->getCcinfo()))
        {
            // if existing card is used
            $payMethodModel = new \WHMCS\Payment\PayMethod\Model();
            $payMethod = $payMethodModel->find($this->model->getCcinfo());

            // get remote token from database
            if($payMethod->payment)
            {
                $sanitizedSensitiveData = $payMethod->payment->getRawSensitiveData();
                $data = json_decode($sanitizedSensitiveData['remoteToken'], true);
                if ($data['customer'])
                {
                    $stripeCustomer   =   \Stripe\Customer::retrieve($data['customer']);
                    $method = \Stripe\PaymentMethod::retrieve($data['method']);
                }
            }
        }

        /**
         * create customer if does not exist
         */
        if(!$stripeCustomer)
        {
            $stripeCustomer = \Stripe\Customer::create(array("description" => "Customer for " . $client->fullName . " (" . $client->email . ")", "email" => $client->email, "metadata" => array("id" => $client->id, "fullName" => $client->fullName, "email" => $client->email)));
        }

        $data = $this->model->getRequestStorage();

        $billingDetails = [];
        if ($data["ccnumber"] || !$method) {
            $billingDetails = ["name" => $data["firstname"] . ' ' . $data["lastname"] , "email" => $data["email"], "address" => ["country" => $data["country"]]];
            if (array_key_exists("address1", $data)) {
                $billingDetails["address"]["line1"] = \WHMCS\Module\Gateway\Stripe\ApiPayload::formatValue($data["address1"]);
            }
            if (array_key_exists("address2", $data)) {
                $billingDetails["address"]["line2"] = \WHMCS\Module\Gateway\Stripe\ApiPayload::formatValue($data["address2"]);
            }
            if (array_key_exists("city", $data)) {
                $billingDetails["address"]["city"] = \WHMCS\Module\Gateway\Stripe\ApiPayload::formatValue($data["city"]);
            }
            if (array_key_exists("state", $data)) {
                $billingDetails["address"]["state"] = \WHMCS\Module\Gateway\Stripe\ApiPayload::formatValue($data["state"]);
            }
            if (array_key_exists("postcode", $data)) {
                $billingDetails["address"]["postal_code"] = \WHMCS\Module\Gateway\Stripe\ApiPayload::formatValue($data["postcode"]);
            }
        }

        if ($data["ccnumber"]) {
            $date = explode('/', $data["ccexpirydate"]);
            $cardNumber = (int)preg_replace('/\s+/', '', $data["ccnumber"]);
            $card = ["number" => $cardNumber, "exp_month" => trim($date[0]), "exp_year" => trim($date[1])];
            if ($data["cccvv"]) {
                $card["cvc"] = $data["cccvv"];
            }

            $method = \Stripe\PaymentMethod::create(["type" => "card", "card" => $card, "billing_details" => $billingDetails]);
        }

        /**
         * attach customer
         */
        if ($method && !$method->customer) {
            $method->attach(array("customer" => $stripeCustomer->id));
            $method->save();
            $stripeCustomer = $stripeCustomer->id;
        }

        //todo save credit card

        if (!$method && $stripeCustomer) {
            $remoteCustomer = \Stripe\Customer::retrieve($stripeCustomer->id);
            $source = $remoteCustomer->default_source;
            if ($source) {
                $method = \Stripe\PaymentMethod::retrieve($source);
            }
        }
        /**
         *
         * create intent
         */
        $intentValues = [
            "amount" => $this->model->getAmount(),
            "currency" => strtolower($this->model->getCurrency()),
            "customer" => $stripeCustomer,
            "payment_method" => $method->id,
            "description" => $this->model->getStatementDescription(),
            "metadata" => [
                "id" => $this->model->getInvoiceid(),
                "invoiceNumber" => $this->model->getInvoicenum()
            ],
            "statement_descriptor_suffix" => $this->model->getStatementDescription(),
            "confirm" => true,
            "off_session" => true
        ];

        if(!$intentValues['statement_descriptor_suffix'])
        {
            unset($intentValues['statement_descriptor_suffix']);
        }

        $intent = \Stripe\PaymentIntent::create($intentValues);

        return $intent;
    }

    /**
     * @throws \Exception
     */
    public function isValid()
    {
        if(!(Request::get('a') == 'checkout' && Request::get('submit') == 'true' && in_array(Request::get('custtype'), ['existing', 'new'])))
        {
            check_token("WHMCS.default", $this->model->getToken());
        }

        if(!$this->model->getStripeToken() && !$this->model->getGatewayid() && !$this->model->getCcinfo())
        {
            global $whmcs;
            throw new \Exception($whmcs->get_lang("creditcarddeclined"));
        }
    }
}
