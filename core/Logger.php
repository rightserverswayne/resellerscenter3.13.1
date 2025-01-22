<?php
namespace MGModule\ResellersCenter\core;

use MGModule\ResellersCenter\repository\Resellers;
use MGModule\ResellersCenter\repository\Logs;

use MGModule\ResellersCenter\Addon;
use MGModule\ResellersCenter\core\Session;


/**
 * Description of Logger
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Logger 
{
    /**
     * Static functioin to create new log messages
     * 
     * @param type $message
     * @param type $clientid
     */
    static public function info($message, $clientid = null)
    {
        $logger = new Logger();
        $logger->addNewLog(Logs::INFO, $message, $clientid);
    }
    static public function warning($message, $clientid = null)
    {
        $logger = new Logger();
        $logger->addNewLog(Logs::WARNING, $message, $clientid);
    }
    static public function error($message, $clientid = null)
    {
        $logger = new Logger();
        $logger->addNewLog(Logs::ERROR, $message, $clientid);
    }

    public static function createLog($action, $params)
    {
        if(array_key_exists($action, self::$successMessages)) 
        {
            $message = self::$successMessages[$action];
            $type = Logs::INFO;
        }
        elseif(array_key_exists($action, self::$failedMessages))
        {
            $message = self::$failedMessages[$action];
            $type = Logs::ERROR;
        }
        else
        {
            $message = "Logger {$action} message is missing";
            $type = Logs::WARNING;
        }
        
        //Get message and swap variables
        foreach($params as &$param) 
        {
            if(is_array($param)) {
                $param = implode(",", $param);
            }
        }
        $parsed = vsprintf($message, $params);
        
        $logger = new Logger();
        $logger->addNewLog($type, $parsed, $params["clientid"]);
    }

    /**
     * Add new log information to database
     * 
     * @param type $type
     * @param type $message
     * @param type $clientid
     */
    public function addNewLog($type, $message, $clientid = null)
    {
        //Get admin ID
        $adminid = Session::get("adminid");

        //Get ResellerID
        if(! Addon::I()->isAdmin()) 
        {
            $resellers = new Resellers();
            $reseller = $resellers->getResellerByClientId(Session::get("uid"));
            
            if(empty($reseller)) {
                $clientid = Session::get("uid");
            }
        }
        
        $repo = new Logs();
        $repo->createNew($type, $message, $adminid, $reseller->id, $clientid);
    }
    
    public static $successMessages = [
        
        //Admin Area
        //Groups
        "groupCreated" => "New resellers group has been created. Group: <a href='?module=ResellersCenter&mg-page=groups'>#%d</a> %s",
        "groupDeleted" => "Reseller group <a href='?addonmodules.php?module=ResellersCenter&mg-page=groups'>#%d</a> has been deleted",
        
        //Contents
        "contentAdded"        => "New content (%s: <a href='?module=ResellersCenter&mg-page=groups'>#%d</a>) has been added to group <a href='?module=ResellersCenter&mg-page=groups'>#%d</a>",
        "contentDeleted"      => "Content <a href='?module=ResellersCenter&mg-page=groups'>#%d</a> has been deleted",
        "contentConfigSaved"  => "Content <a href='?module=ResellersCenter&mg-page=groups'>#%d</a> settings in group <a href='?module=ResellersCenter&mg-page=groups'>#%d</a> has been updated",
        "contentPricingSaved" => "Content <a href='?module=ResellersCenter&mg-page=groups'>#%d</a> pricing has been updated",

        "contentMassiveAdded"        => "New contents has been added to group <a href='?module=ResellersCenter&mg-page=groups'>#%s</a>",
        
        //Resellers
        "resellerCreated"   => "New reseller (client: #%d) has been created and assinged to group #%d",
        "resellerDeleted"   => "Reseller #%d has been deleted",
        "resellerReassign"  => "Reseller #%d has been reassign to group #%d",
        
        //Configuration
        "configurationSaved" => "Configuration has been updated for reseller #%d (0 mean default settings)",
        
        //Clients
        "clientAssigned"   => "Client #%d has been assigned to Reseller #%d",
        "clientUnassinged" => "Client #%d has been usassgined from his reseller",

        //Services
        "serviceAssignedToReseller"     => "Service (%s: #%d) has been assigned to reseller #%d",
        "serviceReassingedToClient"     => "Service (RC id: #%d) has been reassigned to client #%d (belongs to Reseller #%d)",
        "serviceUnassingedFromReseller" => "Service (RC id: #%d) has been unassigned from reseller",
        "servicePricingUpdated"         => "Service (RC id: #%d) pricing has been udpated. New price: %s and billingcycle: %s",
        "serviceTermianted"             => "Service (id: #%d) has been termiated by reseller %s",
        
        //Invoices
        "rcInvoiceCreated"  => "Reseller Invoice #%d has been created",
        "rcInvoicePaid"     => "Reseller Invoice #%d has been paid",
        "rcInvoiceUpdated"  => "Reseller Invoice #%d has been updated",
        "rcInvoiceAddPayment" => "Payment has been added to Reseller Invoice #%d",
        "invoiceUpdated"    => "WHMCS Invoice #%d has been udpated in Resellers Center addon",
        "profitAdded"       => "Profit %s for service #%d has been added to reseller #%d",
        
        //Payouts
        "profitSetAsCollected"    => "Profit #%d has been set manually as collected",
        "singlePayPalPaymentMade" => "Profit #%d has been paid using single PayPal Payout",
        "massPayPalPaymentMade"   => "Mass PayPal payment has been made for resellers: %s",
        "singleCreditPaymentMade" => "Profit #%d has been paid using single Credit Payout",
        "massCreditsPaymentMade"  => "Mass Credits payment has been made for resellers: %s",
        "paypalPayoutsUpdated"    => "PayPal Payouts configuration has been udpated",
        
        
        //Client Area
        //Clients
        "newClientCreated"      => "New client has been created (#%d) and assinged to reseller #%d",
        "unassingClient"        => "Client (RC id #%d) has been unassinged from his reseller",
        "loggedAsClient"        => "Reseller has logged as client RC id #%d",
        "makingOrderFor"        => "Reseller is making order for client RC id #%d",
        "clientProfileUpdated"  => "Client #%d (RC id #%d) profile information has been updated",
        
        //Configuration
        "configurationPrivateSaved" => "Reseller #%d private configuration has been updated",
        "logoUploaded" => "Logo %s has been uploaded",
        
        //Orders
        "resellerInvoiceCreated" => "Order #%d payment status is unpaid. Invoice #%d for reseller has been created",
        "orderAccepted"     => "Order #%d has been accepted by reseller",
        "orderDeleted"      => "Order #%d has been deleted by reseller",
        "orderCancelled"    => "Order #%d has been cancelled by reseller",
        "orderMarkAsFraud"  => "Order #%d has been mark as fraud by reseller",
        
        //Pricing
        "pricingUpdated" => "Reseller #%d pricing has been updated for %s: #%d",
        "pricingDeleted" => "Reseller #%d pricing (#%d) has been deleted",
        
        //Hooks
        "newServiceRelation" => "New service (%s: #%d) relation has been added to reseller #%d",
        "brandedEmailSent"  => "Branded email (template: %s) has been sent using reseller #%d for branding",
        "newTicketRelation" => "New ticket (#%d) has been assigned to reseller #%d",
        "newTicketReply"    => "New ticket (#%d) response has been sent",
    ];
    
    public static $failedMessages = [
        "orderCancelFailed" => "Unable to cancel order. Error: %s",
        "orderFraudFailed" => "Unable to set order as fraud. Error: %s",
    ];
}