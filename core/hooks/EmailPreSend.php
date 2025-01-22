<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\core\emailTemplatesFields\FieldsFactory;
use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\Core\Whmcs\Invoices\InvoiceItem;
use MGModule\ResellersCenter\Core\Whmcs\Products\Upgrades\Upgrade;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings\EnableConsolidatedInvoices;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings\EndClientConsolidatedInvoices;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\Services\ConsolidatedInvoiceService;
use MGModule\ResellersCenter\Loader;

use MGModule\ResellersCenter\repository\EmailTemplates as RCEmailTemplate;
use MGModule\ResellersCenter\repository\ResellersTickets;

use MGModule\ResellersCenter\repository\whmcs\InvoiceItems;
use MGModule\ResellersCenter\repository\whmcs\Invoices as WhmcsInvoices;
use MGModule\ResellersCenter\repository\whmcs\Tickets;
use MGModule\ResellersCenter\repository\whmcs\Invoices;
use MGModule\ResellersCenter\repository\whmcs\TicketReplies;
use MGModule\ResellersCenter\repository\whmcs\EmailTemplates;

use MGModule\ResellersCenter\core\Mailer;
use MGModule\ResellersCenter\core\MergeFields;
use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\core\Session;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;

use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use \MGModule\ResellersCenter\repository\ResellersSettings;

use MGModule\ResellersCenter\core\WHMCSGlobalConfig;

use Michelf\Markdown;

/**
 * Description of AdminAreaPage
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class EmailPreSend
{
    const ORDER_CONFIRMATION_TYPE = 'Order Confirmation';
    const INVOICE_CREATED = 'Invoice Created';
    const SUPPORT_TICKET_REPLY = 'Support Ticket Reply';
    const SUPPORT_TICKET_OPENED = 'Support Ticket Opened';
    const SUPPORT_TICKET_OPENED_BY_ADMIN = 'Support Ticket Opened by Admin';
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     *
     * @var array
     */
    public $functions;

    /**
     * hook params
     *
     * @var type
     */
    public static $params;

    /**
     * Assign anonymous function
     */
    public function __construct()
    {
        require __DIR__.DS."..".DS."..".DS."Loader.php";
        new Loader();

        $this->functions[0] = function($params) {
            self::$params = $this->addAttachmentsToTicket($params);
        };

        $this->functions[10] = function($params) {
            self::$params = $this->createUpgradeInvoiceItems($params);
        };

        $this->functions[PHP_INT_MAX] = function($params) {
            return $this->sendBrandedEmail(self::$params);
        };
    }

    /**
     * Add attachments to tickets and ticket replies.
     *
     * @param type $params
     * @return type
     */
    public function addAttachmentsToTicket($params)
    {
        $messagename = $params["messagename"];
        $ticketid    = $params["relid"];

        //Run this hook only for message that we want to
        if($messagename != self::SUPPORT_TICKET_REPLY && $messagename != self::SUPPORT_TICKET_OPENED_BY_ADMIN)
        {
            return $params;
        }

        //Check if there are any attachments to attach
        $attachments = Session::get("attachments");
        if(empty($attachments))
        {
            return $params;
        }

        if($messagename == self::SUPPORT_TICKET_REPLY)
        {
            $repo = new TicketReplies();
            $replies = $repo->getRepliesByTicketId($ticketid);
            $ticket = $replies->first(); //This is last reply!
        }
        else //Support Ticket Opened by Admin
        {
            $repo = new Tickets();
            $ticket = $repo->find($ticketid);
        }

        $ticket->attachment = $attachments;
        $ticket->save();

        return $params;
    }

    public function createUpgradeInvoiceItems($params)
    {
        global $whmcs;
        global $CONFIG;

        $reseller = \MGModule\ResellersCenter\Core\Helpers\Reseller::getCurrent();
        if ($reseller->exists && $params["messagename"] == self::ORDER_CONFIRMATION_TYPE &&
            basename(Server::get("SCRIPT_NAME")) == "upgrade.php" && Request::get("type") != "configoptions")
        {
            $upgradeUid = Session::getAndClear("RC_UpgradeUid");
            $params['upgradeUid'] = $upgradeUid;
            $originUid = Session::get("uid");

            $paymentMethod = !$reseller->settings->admin->resellerInvoice ? Request::get("paymentmethod") : "";

            $repo = new InvoiceItems();

            $repo->deleteNotAssignedByClientId($originUid);

            $client = new Client($upgradeUid);
            //$client->login(); // WHMCS 8.8
            Session::set("uid", $upgradeUid);

            $upgrade = new Upgrade(Session::get("upgradeids")[0], $reseller);
            $product = $upgrade->getNewProduct();
            $pricing = $upgrade->getPricing($client->getCurrency());
            if (Session::get("RC_UpgradeStartNewPeriod")) {
                $pricing->setStartNewPeriodFlag();
            }

            $price = $pricing->getResellerPrice(Session::get("RC_OldBillingCycle"));
            if ($price > 0) {
                $itemParams['invoiceid'] = 0;
                $itemParams['userid'] = $client->id;
                $itemParams['type'] = InvoiceItems::TYPE_UPGRADE;
                $itemParams['relid'] = $upgrade->id;
                $itemParams['description'] = $upgrade->getOrderDescription();
                $itemParams['amount'] = $price;
                $itemParams['taxed'] = $product->tax;
                $itemParams['duedate'] = date("Y-m-d", strtotime("+ {$whmcs->get_config("CreateInvoiceDaysBefore")} Days"));
                $itemParams['paymentmethod'] = $paymentMethod;

                $item = new InvoiceItem();
                $item->create($itemParams);
            } else {
                if ($CONFIG["CreditOnDowngrade"]) {
                    if (SettingsManager::getSettingFromReseller($reseller, EndClientConsolidatedInvoices::NAME) != 'on' ||
                        SettingsManager::getSettingFromResellerClient($client->resellerClient, EnableConsolidatedInvoices::NAME) != 'on') {
                        $refund = $price * (-1);
                        $refund =  round($refund, 2);
                        $reseller->transferCreditToClient($refund, $client->id, "ResellersCenter Refund for Upgrade #{$upgrade->id}.");
                    }
                }
            }

            //Include tax for order amount
            if ($whmcs->get_config("TaxEnabled") && $product->tax) {
                $price = Helper::includeTax($price, $client->tax->taxrate, $client->tax2->taxrate);
            }

            //Update Order & upgrade
            $upgrade->order->userid = $client->id;
            $upgrade->order->amount = $price;
            $upgrade->order->save();

            $upgrade->amount = $price;
            $upgrade->save();
        }

        return $params;
    }

    /**
     * Send email from reseller to his client. Messages are send via WHMCS API
     * or via WHMCS function sendMessage in case of a support message
     *
     * @param type $params
     */
    public function sendBrandedEmail($params)
    {
        // prevent against loop in send emails
        if (WhmcsAPI::isRcAPiRequest()) {
            return $params;
        }

        if ($params["mergefields"]['forceSend']) {
            return $params;
        }

        $templates = new EmailTemplates();
        $message = $templates->getByName($params["messagename"], "");

        //replace user ID only for Order Confirmation from upgrade order   resellerID -> endClientId
        $params['relid'] = $params['upgradeUid'] ?? $params['relid'];

        if ($message->type == EmailTemplates::MSG_ADMIN) {
            return $params;
        }

        $relid = $params['relid']; //save relid param

        //Return if we have been here already!
        if (Session::get("sendResellerMessage".$message->name)) {
            Session::clear("sendResellerMessage".$message->name);
            return $params;
        }

        /**
         * Those email templates are send in different place
         */
        if (Session::getAndClear("preventSend_".$message->name)) {
            $params["abortsend"] = true;
            return $params;
        }

        $reseller = $this->getResellerByMessage($message, $params["relid"]);

        if ($message->type == EmailTemplates::MSG_INVOICE) {

            $invoices = new WhmcsInvoices();
            $invoice = $invoices->find($params["relid"]);
            $service = new ConsolidatedInvoiceService();

            if ($service->isConsolidatedInvoice($invoice)) {
                $params["abortsend"] = true;
                return $params;
            }
        }

        if (!$reseller->exists) {
            return $params;
        }

        $resellerEmailTemplates = $reseller->settings->admin->emailTemplates ?: [];

        $client = $this->getClientByMessage($message->type, $params["relid"]);

        if ($reseller->settings->admin->sendDefaultEmails && !array_key_exists($message->name, $resellerEmailTemplates)) {
            $this->sendViaMailerToReseller($reseller, $client, $message, $params["relid"]);
            return $params;
        }

        if (!array_key_exists($message->name, $resellerEmailTemplates)) {
            $params["abortsend"] = true;
            return $params;
        }

        $params['relid'] = $relid;  //restore relid param

        //Send message
        if (!Session::getAndClear("DoNotBrand_".$message->name))
        {
            if(\MGModule\ResellersCenter\Core\Helpers\Reseller::isMakingOrderForClient()
                && Whmcs::isVersion('8.0')
                && $message->type === EmailTemplates::MSG_GENERAL)
            {
                $params['relid'] = $_SESSION['makeOrderFor'];
            }

            global $CONFIG;
            $templateLanguage = $client->language;
            if ($templateLanguage === null) {
                $templateLanguage = $_SESSION['Language'] ?: $CONFIG['Language'];
            }

            //Use reseller template if it is enabled by admin - otherwise send message via WHMCS API
            if ($resellerEmailTemplates[$message->name]) {
                //Get template
                $rcTemplates = new RCEmailTemplate();
                $template = $rcTemplates->getByName($reseller->id, $message->name, $templateLanguage);

                //If reseller did not brand templates use WHMCS template
                if (empty($template)) {
                    $template = $templates->getByName($message->name, $templateLanguage);
                }
                $message->subject = $template->subject;
                $message->message = ($template->editor == 'markdown') ? Markdown::defaultTransform($template->message) : $template->message;
                $result = $this->sendViaMailer($reseller, $client, $message, $params["relid"]);
            } else {
                Session::set("sendResellerMessage".$message->name, true);
                WHMCSGlobalConfig::storeConfig(["SystemURL", "LogoURL", "Signature", "Email", "CompanyName", "Domain"]);
                WHMCSGlobalConfig::storeEmailConfig();
                $result = $this->sendViaAPI($reseller, $message, $params["relid"]);
                WHMCSGlobalConfig::restoreConfig(["SystemURL", "LogoURL", "Signature", "Email", "CompanyName", "Domain"]);
                WHMCSGlobalConfig::restoreEmailConfig();
            }

            $this->sendViaMailerToReseller($reseller, $client, $message, $params["relid"]);

            $params["abortsend"] = $result;
        }

        if($params["abortsend"])
        {
            Session::set("sentByResellersCenter", true);
            EventManager::call("brandedEmailSent", $message->name, $reseller->id);
        }

        return $params;
    }

    private function sendViaMailerToReseller($reseller, $client, $message, $relid)
    {
        $repo = new ResellersSettings();
        $emailRedirectAllowed = $repo->getSetting('emailredirect', $reseller->id);

        if ($emailRedirectAllowed != 'on') {
            return;
        }

        $data = $this->getParamsForMailer($reseller, $client, $message, $relid);

        $params         = $data['params'];
        $attachments    = $data['attachments'];

        $receiver = array("userid" => $reseller->client_id, "email" => $reseller->client->email, "name" => $reseller->client->firstname . " " . $reseller->client->lastname);
        Mailer::sendMail($reseller, $message, $receiver, [], "", $params, $attachments);

        return true;
    }

    private function sendViaMailer($reseller, $client, $message, $relid)
    {
        $data = $this->getParamsForMailer($reseller, $client, $message, $relid);

        $receiverEmail  = $data['receiverEmail'];
        $params         = $data['params'];
        $attachments    = $data['attachment'];
        
        $sender = ["email" => $reseller->settings->private->email, "name" => $reseller->settings->private->companyName];

        $receiver = ["userid" => $client->id, "email" => $receiverEmail ?: $client->email, "name" => $client->firstname . " " . $client->lastname];

        Mailer::sendMail($reseller, $message, $receiver, $sender, "", $params, $attachments);

        return true;
    }

    private function getParamsForMailer($reseller, $client, $message, $relid)
    {
        $attachments = [];
        $receiverEmail = '';

        $fieldObject = FieldsFactory::create($message->type);
        $params = $fieldObject->getParams($reseller, $client, $message, $relid);
        $attachmentsField = $fieldObject->getAttachments($relid);
        $receiverEmail = $fieldObject->getReceiverEmail($params) ?? $receiverEmail;

        $attachments = !empty($attachmentsField) && !is_array($attachmentsField) ? $attachments[] = $attachmentsField : $attachmentsField;
        
        return [
            'params'        => $params,
            'attachment'    => $attachments,
            'receiverEmail' => $receiverEmail
        ];
    }

    /**
     * Send email message using WHMCS API or WHMCS sendMessage function
     * Used reseller did not brand email templates.
     *
     * @param \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller
     * @param type $message
     * @param type $relid
     * @return type
     */
    private function sendViaAPI($reseller, $message, $relid)
    {
        //Get Resellers configuration and set/get email params
        $emailParams = $this->getBrandedParams($reseller->id);

        //Special case
        $fields = new MergeFields();
        if ($message->name == self::ORDER_CONFIRMATION_TYPE) {
            $emailParams = array_merge($emailParams, $fields->getOrderRelatedFields($relid));
        } elseif ($message->name == self::INVOICE_CREATED) {
            $invoices = new Invoices();
            $invoice = $invoices->find($relid);
            if ($reseller->settings->admin->invoiceBranding && !empty($invoice->branded->invoicenum)) {
                $emailParams = array("invoice_num" => $invoice->branded->invoicenum);
            }
        }
        //Send message - if it is support msg the use WHMCS function
        if ($message->type == EmailTemplates::MSG_SUPPORT) {
            $repo = new ResellersTickets();
            $ticket = $repo->getByRelId($relid);

            if (!empty($ticket->ticket->replies)) {
                $sorted = $ticket->ticket->replies->sortByDesc("date");
                $lastReply = $sorted->first();
            }

            $result = sendMessage($message->name, $relid, ["ticket_reply_id" => $lastReply ? $lastReply->id : null]);
        } else {
            if ($reseller->hasCustomMailBox()) {
                $client = $this->getClientByMessage($message->type, $relid);
                $result = $this->sendViaMailer($reseller, $client, $message, $relid);
            } else {
                $result = WhmcsAPI::request("sendemail", array(
                    "id" => $relid,
                    "messagename" => $message->name,
                    "customvars" => $emailParams
                ));
            }
        }

        return (bool)$result;
    }

    /**
     * Get Reseller based on message name and relid
     *
     * @param type $message
     * @param type $relid
     * @return MGModule\ResellersCenter\Core\Resources\Resellers\Reseller
     */
    private function getResellerByMessage($message, $relid)
    {
        if (
            (
                in_array('/account/users/invite', [Request::get('rp'), Server::get('PATH_INFO')])
                || in_array('/account/users/invite/resend', [Request::get('rp'), Server::get('PATH_INFO')])
            )
            && Session::get('branded') === true)
        {
            return Session::get('loggedAsClient') ? new Reseller(Session::get('loggedAsClient')) : ResellerHelper::getCurrent();
        }

        //Force Reseller
        if (Request::get("resellerid") || Session::get("rcResellerId")) {
            $resellerId = Request::get("resellerid") ?: Session::getAndClear("rcResellerId");
            $reseller = new Reseller($resellerId);
            return $reseller;
        }

        try {
            if (empty($message)) {
                throw new \Exception('');
            }
            $fieldObject = FieldsFactory::create($message->type);
            return $fieldObject->getReseller($relid, $message);
        } catch (\Exception $e) {
            return new Reseller(NULL);
        }
    }

    /**
     * Get client based on message type and relid
     *
     * @param type $type
     * @param type $relid
     * @return type
     */
    private function getClientByMessage($type, $relid)
    {
        $fieldObject = FieldsFactory::create($type);
        $client = $fieldObject->getClient($relid);

        return $client;
    }

    /**
     * Prepare global variables for email
     * and set from fields and other global settings
     *
     * @global type $CONFIG
     * @param type $settings
     * @return string
     */
    private function getBrandedParams($resellerid)
    {
        global $CONFIG;
        global $fromname;
        global $fromemail;

        $fields = new MergeFields();
        $params = $fields->getOtherFields($resellerid);

        $whmcsURL = parse_url($CONFIG["SystemURL"]);
        $domainURL = parse_url($params["company_domain"]);
        $CONFIG["Signature"] = $params["signature"];
        $CONFIG["SystemURL"] = $CONFIG["Domain"] = "{$whmcsURL["scheme"]}://{$domainURL['host']}{$whmcsURL["path"]}";
        $CONFIG["LogoURL"]   = $params["company_logo_url"];

        $reseller = new Reseller($resellerid);
        $fromemail = $CONFIG['Email'] = $reseller->settings->private->email;
        $fromname  = $CONFIG['CompanyName'] = $reseller->settings->private->companyName;

        return $params;
    }
}
