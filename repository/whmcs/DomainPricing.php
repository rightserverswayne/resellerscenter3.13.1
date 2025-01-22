<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use MGModule\ResellersCenter\repository\source\AbstractRepository;

use MGModule\ResellersCenter\models\whmcs\DomainPricing as DomainPricingModel;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of DomainPricing
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class DomainPricing extends AbstractRepository
{    
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\DomainPricing';
    }
    
    public function getByTld($tld)
    {
        if(substr($tld, 0, 1) != ".") {
            $tld = ".".$tld;
        }
        
        $model = new DomainPricingModel();
        $domain = $model->where("extension", $tld)->first();
        
        return $domain;
    }
           
    public function getAvailableTlds()
    {
        $query = DB::table("tbldomainpricing");
        $result = $query->get();
        
        return $result;
    }
}
