<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Addon;

use MGModule\ResellersCenter\Core\Helpers\AdminAreaHelper;
use MGModule\ResellersCenter\core\Session;
use MGModule\ResellersCenter\core\Server;

use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\mgLibs\Smarty;
use MGModule\ResellersCenter\repository\ResellersClients;

/**
 * Description of AdminAreaPage
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class AdminAreaHeaderOutput
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
        $this->functions[100] = function() {
            return $this->loadAdminAreaScripts();
        };      
    }
    
    public function loadAdminAreaScripts()
    {
        $script = '';
        $scriptName = Server::get("SCRIPT_NAME");
        if(basename($scriptName) != 'addonmodules.php')
        {
            //set WHMCS url
            global $CONFIG;
            $script .= "<script type='text/javascript'>var whmcsUrl = '{$CONFIG["SystemURL"]}';</script>";

            //hide Aborted By hook message
            $sentByResellersCenter = Session::get("sentByResellersCenter") ? 1 : 0;
            $script .= "<script type='text/javascript'>var sentByResellersCenter = {$sentByResellersCenter};</script>";
            
            $script .= "<script type='text/javascript'>".file_get_contents(Addon::getMainDIR().DS."templates".DS."admin".DS."assets".DS."js".DS."AdminAreaController.js")."</script>";
            $script .= "<script type='text/javascript'>".file_get_contents(Addon::getMainDIR().DS."templates".DS."admin".DS."assets".DS."js".DS."mgLibs.js")."</script>";

            $script .= AdminAreaHelper::getJavaScriptControllers();

            //Insert languages
            Addon::I();            
            $script = Smarty::I()->fetchString($script, ['MGLANG' => Lang::getInstance()]);;
        }
        
        return $script;
    }
}
