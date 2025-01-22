<?php
namespace MGModule\ResellersCenter\gateways\Stripe\Components\Models;
use MGModule\ResellersCenter\gateways\Stripe\Components\Interfaces\AbstractRequestModel;

/**
 *
 * Created by PhpStorm.
 * User: Tomasz Bielecki ( tomasz.bi@modulesgarden.com )
 * Date: 04.03.20
 * Time: 09:08
 * Class StripeRequest
 */
class StripeRequest extends AbstractRequestModel
{

    protected $token;
    protected $action;
    protected $invoiceid;
    protected $ccinfo;
    protected $billingcontact;
    protected $firstname;
    protected $lastname;
    protected $address1;
    protected $address2;
    protected $city;
    protected $state;
    protected $postcode;
    protected $countryCallingCodePhonenumber;
    protected $phonenumber;
    protected $ccdescription;
    protected $stripeToken;
    protected $amount;
    protected $currency;
    protected $statementDescription;
    protected $invoicenum;
    protected $gatewayid;
    protected $custtype;
    protected $paymentmethod;

    /**
     * @return mixed
     */
    public function getInvoicenum()
    {
        return $this->invoicenum;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return mixed
     */
    public function getInvoiceid()
    {
        return $this->invoiceid;
    }

    /**
     * @return mixed
     */
    public function getCcinfo()
    {
        return $this->ccinfo;
    }

    /**
     * @return mixed
     */
    public function getBillingcontact()
    {
        return $this->billingcontact;
    }

    /**
     * @return mixed
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @return mixed
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @return mixed
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return mixed
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @return mixed
     */
    public function getCountryCallingCodePhonenumber()
    {
        return $this->countryCallingCodePhonenumber;
    }

    /**
     * @return mixed
     */
    public function getPhonenumber()
    {
        return $this->phonenumber;
    }

    /**
     * @return mixed
     */
    public function getCcdescription()
    {
        return $this->ccdescription;
    }

    /**
     * @return mixed
     */
    public function getStripeToken()
    {
        return $this->stripeToken;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return mixed
     */
    public function getStatementDescription()
    {
        return $this->statementDescription;
    }

    /**
     * @return mixed
     */
    public function getGatewayid()
    {
        return $this->gatewayid;
    }

    public function getCustType()
    {
        return $this->custtype;
    }

    public function getPaymentMethod()
    {
        return $this->paymentmethod;
    }

}