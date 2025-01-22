<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Clients\Traits;

use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;
use MGModule\ResellersCenter\repository\whmcs\Clients;

/**
 * Description of CreditCard
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
Trait CreditCard
{
    public function getCCDetails()
    {
        $repo = new Clients();
        return $repo->getDecryptedCreditCard($this->id);
    }
    
    public function updateCCDetails($cardtype, $lastfour, $expdate, $cardnum = null, $startdate = null, $issuenumber = null, $gatewayid = null, $force = false, $ccdescription = '', $gatewayName = '')
    {
        $client = \WHMCS\User\Client::findOrFail($this->id);
        $payMethod = \WHMCS\Payment\PayMethod\Adapter\RemoteCreditCard::factoryPayMethod($client, $client, $ccdescription, true);

        $gatewayInterface = new \WHMCS\Module\Gateway();
        $gatewayInterface->load($gatewayName);
        $payMethod->setGateway($gatewayInterface);
        $payMethod->save();
        $newPayment = $payMethod->payment;

        $newPayment->setLastFour($lastfour);
        $newPayment->setCardType($cardtype);
        $newPayment->setExpiryDate( \WHMCS\Carbon::createFromCcInput($expdate));
        $newPayment->setRemoteToken($gatewayid)->save();

        //Save gatewayid - it is used by some gateways
        //to create charges without rechecking CC details
        if($gatewayid !== null)
        {
            $this->gatewayid = $gatewayid;
            $this->save();
        }
    }
}
