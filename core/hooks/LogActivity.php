<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\Core\Whmcs\Logs\ActivityLog;

/**
 * Description of LogActivity
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class LogActivity
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     * 
     * @var array 
     */
    public $functions;
    
    /**
     * Container for hook params
     * 
     * @var type 
     */
    public static $params;
    
    /**
     * Assign anonymous function
     */
    public function __construct() 
    {
        $this->functions[10] = function($params)
        {
            self::$params = $this->fixAbortByHookMessage($params);
        };
    }
    
    /**
     * Add Ticket relation to reseller
     * 
     * @param type $params
     * @return type
     */
    public function fixAbortByHookMessage($params)
    {
        //This should methond should run just after sending email
        if(!Session::get("sentByResellersCenter") || strpos($params["description"], "Email Sending Aborted by Hook") === false)
        {
            return $params;
        }

        if(basename(Server::get("SCRIPT_NAME")) == "cron.php")
        {
            Session::clear("sentByResellersCenter");
        }

        $log = ActivityLog::getByDescription("Email Sending Aborted by Hook");

        $description = str_replace("Email Sending Aborted by Hook", "Email sent by Resellers Center addon", $log->description);
        $log->description = $description;
        $log->save();
    }
}
