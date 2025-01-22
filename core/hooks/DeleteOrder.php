<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\repository\whmcs\Domains;
use MGModule\ResellersCenter\repository\whmcs\Hostings;
use MGModule\ResellersCenter\repository\whmcs\HostingAddons;
use MGModule\ResellersCenter\repository\ResellersServices;

/**
 * Description of DeleteOrder
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class DeleteOrder 
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
            return $this->removeSerivceRelation($params);
        };
    }
    
    /**
     * Remove relation between reseller and service
     * 
     * @param type $params
     * @return type
     */
    public function removeSerivceRelation($params)
    {
        $serviceRepo = new ResellersServices();
        
        //Find products
        $hostingRepo = new Hostings();
        $hostings = $hostingRepo->getByOrderId($params["orderid"]);
        foreach($hostings as $hosting)
        {
            if(! empty($hosting->resellerService))
            {
                $serviceRepo->delete($hosting->resellerService->id);
            }
        }
        
        //Addons
        $addonsRepo = new HostingAddons();
        $addons = $addonsRepo->getByOrderId($params["orderid"]);
        foreach($addons as $addon)
        {
            if(! empty($addon->resellerService))
            {
                $serviceRepo->delete($addon->resellerService->id);
            }
        }
        
        //Domains
        $domainsRepo = new Domains();
        $domains = $domainsRepo->getByOrderId($params["orderid"]);
        foreach($domains as $domain)
        {
            if(! empty($domain->resellerService))
            {
                $serviceRepo->delete($domain->resellerService->id);
            }
        }
    }
}
