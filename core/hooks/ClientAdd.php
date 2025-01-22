<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\ClientAreaHelper;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\repository\whmcs\Clients;
use MGModule\ResellersCenter\repository\whmcs\EmailTemplates;
use MGModule\ResellersCenter\repository\EmailTemplates as RCEmailTemplates;
use MGModule\ResellersCenter\repository\ResellersClients;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\core\helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\core\MergeFields;
use MGModule\ResellersCenter\core\Mailer;

use MGModule\ResellersCenter\core\Session;

/**
 * Description of ClientAdd
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ClientAdd 
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     * 
     * @var array 
     */
    public $functions;
    
    /**
     * Assign anonymous function
     */
    public function __construct() 
    {
        $this->functions[10] = function($params)
        {
            return $this->storeClientPassword($params);
        };

        $this->functions[20] = function($params)
        {
            return $this->logInClient($params);
        };
    }
    
    /**
     * Store decrypted client password in database
     * And create client ↔ reseller relation 
     * 
     * @param type $params
     * @return type
     */
    public function storeClientPassword($params)
    {
        $ruid = Session::getAndClear("ResellerAddClient");
        $reseller = $ruid ? new Reseller(null, $ruid) : ResellerHelper::getCurrent();
        if(!$reseller->exists)
        {
            return;
        }

        /**
         * This hook is fired after ClientLogin hook...
         * Relation client ↔ reseller is added only if client is created via AA
         */
        $repo = new ResellersClients();
        $relation = $repo->getByRelid($params["userid"]);

        //Check if new relation should be created
        if(empty($relation))
        {
            $repo->createNew($reseller->id, $params["userid"]);
        }

        /**
         * Do not send email when noemail flag is set
         * This is used it during client creation in Reseller Area when option
         */
        if(Session::get('createClient_noEmail', false) == true)
        {
            Session::set('createClient_noEmail', false);

            return $params;
        }

        /**
         * Send Email
         */
        $fields = new MergeFields();
        $vars = $fields->getFieldsValues($reseller->id, $params["userid"]);
        $vars["client_password"] = $params["password"];

        $clients = new Clients();
        $client = $clients->find($params["userid"]);

        $sender = array("email" => $reseller->settings->private->email, "name" => $reseller->settings->private->companyName);
        $reciever = array("userid" => $params["userid"] ,"email" => $params["email"], "name" => "{$params["firstname"]} {$params["lastname"]}");

        //Get Template
        $whmcsTemplates = new EmailTemplates();
        $message = $whmcsTemplates->getByName("Client Signup Email", $client->language);

        $rcTemplates = new RCEmailTemplates();
        $template = $rcTemplates->getByName($reseller->id, "Client Signup Email", $client->language);
        if(! empty($template) && $reseller->settings->admin->emailTemplates["Client Signup Email"])
        {
            $message->subject = $template->subject;
            $message->message = $template->message;
        }

        //Session::set("preventSend_".$message->name, 1);
        Mailer::sendMail($reseller, $message, $reciever, $sender, "", $vars);

        //Client Email Address Verification
        if(!Whmcs::isVersion('8.0'))
        {
            $emailVerificationTpl = 'Client Email Address Verification';
            $message = $whmcsTemplates->getByName($emailVerificationTpl, $client->language);
            $template = $rcTemplates->getByName($reseller->id, $emailVerificationTpl, $client->language);
            if (!empty($template) && $reseller->settings->admin->emailTemplates[$emailVerificationTpl]) {
                $message->subject = $template->subject;
                $message->message = $template->message;
            }

        //Session::set("Client Email Address Verification_".$message->name, 1);
        Mailer::sendMail($reseller, $message, $reciever, $sender, "", $vars);
        }

        return $params;
    }

    /**
     * If client comes from register page log him in
     */
    protected function logInClient($params)
    {
        $reseller = ResellerHelper::getCurrent();
        if($reseller->exists && ClientAreaHelper::isClientArea())
        {
            $client = new Client($params["userid"]);
            $client->login();
        }
    }
}
