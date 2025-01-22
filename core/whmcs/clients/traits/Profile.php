<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Clients\Traits;

use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;
use MGModule\ResellersCenter\mgLibs\exceptions\WhmcsAPI as WhmcsApiException;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Request;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\models\whmcs\Client;
use MGModule\ResellersCenter\models\whmcs\Contact;

/**
 * Description of profile
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
Trait Profile
{
    public function create($data)
    {
        //Check if email is unique - WHMCS does NOT validates that when `Locked Client Profile Fields` are checked
        $client     = (new Client())->byEmail($data["email"])->first();
        $contact    = (new Contact())->byEmail($data["email"])->first();
        if($client->exists || $contact->exists)
        {
            throw new WhmcsApiException("The email address entered is already in use by another client account and must be unique for each client");
        }

        $data['noemail'] = $data['sendWelcomeMsg'] == 'on' ? false : true;
        if($data['noemail'])
        {
            \MGModule\ResellersCenter\Core\Session::set("createClient_noEmail", true);
        }

        try {
            /* For preventing WHMCS8 addClient API method User log out after error */
            $savedSession = \MGModule\ResellersCenter\Core\Session::store();

            $result = WhmcsAPI::request('addclient', $data);
        } catch (WhmcsApiException $e)
        {
            /* Restoring previously saved session */
            \MGModule\ResellersCenter\Core\Session::restore($savedSession);

            throw new WhmcsApiException($e->getMessage());

        }

        /* Updating a new client currency field due to addClient WHMCS8 API bug */
        $newClient = \WHMCS\User\Client::find($result["clientid"]);
        $newClient->currency = (int)$data['currency'];
        $newClient->save();

        return $result["clientid"];
    }
    
    public function update($data)
    {
        if(empty($data["password2"])) 
        {
            unset($data["password2"]);
        }

        $data["clientid"] = $this->id;
        WhmcsAPI::request('updateclient', $data);

        if(Whmcs::isVersion('8.00'))
        {
            $client = \WHMCS\User\Client::find($this->id);
            WhmcsAPI::request('updateuser', [
                'user_id' => $client->owner()->id,
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $data['email']
            ]);
        }
    }
    
    public function getCountryName()
    {
        //Include countries list
        $countries = Whmcs::getCountries();
        
        $country = $countries[$this->country];
        return $country;
    }
    
    public function resetPassword($session = null)
    {
        if(!Whmcs::isVersion('8.0'))
        {
            $reseller = Reseller::getLogged();
            Request::set("resellerid", $reseller->id);
        }

        if(Whmcs::isVersion('8.0'))
        {
            $client = \WHMCS\User\Client::find($this->id);
            $res = WhmcsAPI::request('resetPassword', ['id' => $client->owner()->id]);

            /* Restoring if isset */
            if($session)
            {
                Session::restore($session);
            }

        } else
        {
            $client = new \WHMCS\Client($this->id);
            $client->setID($this->id);
            $res = $client->resetSendPW();
        }

        if((!$res || $res['result'] == 'error') && $res['email'] != 'Email Send Aborted By Hook')
        {
            throw new \Exception("unableToResetPw");
        }
    }
}
