<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\repository\whmcs\Clients;

use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\core\helpers\ClientAreaHelper as CAHelper;
use MGModule\ResellersCenter\core\Session;

use MGModule\ResellersCenter\mgLibs\Smarty;
use MGModule\ResellersCenter\Addon;

/**
 * Description of AdminAreaPage
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ClientAreaHeadOutput
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
        $this->functions[10] = function($params) {
            return $this->addInfoAboutClient($params);
        };
        
        $this->functions[20] = function($params) {
            return $this->addLogoutFromClient($params);
        };

        $this->functions[30] = function($params) {
            return $this->removeCheckoutCreateNewAccOption($params);
        };
    }
    
    /**
     * Add script to display information about that reseller is logged as client
     * and link to logout and return to Resellers Center in CA.
     * 
     * @param type $params
     */
    public function addLogoutFromClient()
    {
        //Check if reseller is logged as client
        if(empty(Session::get("loggedAsClient"))) {
            return;
        }
        
        Addon::I();
        $repo = new Clients();
        $client = $repo->find(Session::get("loggedAsClient"));
       
        Smarty::I()->setTemplateDir(Addon::getMainDIR()."/templates/clientarea/default/misc");
        $template = Smarty::I()->view("clientLogout", array("client" => $client));
        
        return $template;
    }
    
    
    /**
     * Add script that will display information that
     * current order will be made for reseller's client
     * 
     * @param type $params
     * @return string
     */
    public function addInfoAboutClient()
    {
        if(! Reseller::isMakingOrderForClient())
        {
            return;
        }
        
        //We need to load whole addon to use smarty...
        Addon::I();
        
        $repo = new Clients();
        $client = $repo->find(Session::get("makeOrderFor"));
       
        Smarty::I()->setTemplateDir(Addon::getMainDIR()."/templates/clientarea/default/misc");
        $template = Smarty::I()->view("orderAlert", array("client" => $client));
            
        return $template;
    }


    /**
     * Hide 'Create a New Account' when
     * ordering with 'makeOrderFor' option in WHMCS8+
     *
     * @param type $params
     */
    private function removeCheckoutCreateNewAccOption()
    {
        if (!Reseller::isMakingOrderForClient() && Whmcs::isVersion('8.0')) {
            return;
        }

        if ((basename(Server::get('SCRIPT_NAME')) == 'cart.php')
            && (Request::get('a') == 'checkout'))
        {
            return "<script type='text/javascript'>
                        jQuery(document).ready(function(){
                            jQuery('input[value=new]').closest('.col-sm-12').remove();
                            
                            jQuery('input[name=\"paymentmethod\"]').on('ifChecked', function(event){
                                if(jQuery(this).hasClass('is-credit-card')){    
                                    jQuery('#creditCardInputFields').toggle(function() {
                                    }, function() {
                                        jQuery('#creditCardInputFields').stop(true, true).slideUp('fast');
                                    });
                                }
                             });
                            //It blocks stripe initial payment request.    
                            jQuery('#btnCompleteOrder').closest('form').click(function(event) {
                                if(jQuery('input[name=\"paymentmethod\"]:checked').val().toLowerCase() == 'stripe'){
                                    jQuery(this).off('submit.stripe'); 
                                }
                            });
    
                        });
                        </script>";
        }
    }
}
