<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Relations;
use MGModule\ResellersCenter\repository\ResellersServices;

/**
 * Description of Domains
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Domains extends AbstractRelations
{
    protected function getRepo()
    {
        return new ResellersServices();
    }
    
    public function getByClient($clientid)
    {
        $repo = $this->getRepo();
        $result = $repo->getServicesByClientId($clientid, ResellersServices::TYPE_DOMAIN, $this->reseller->id);

        return $result;
    }
    
    public function getForTable($dtRequest, $clientid = null)
    {
        $repo = $this->getRepo();
        $result = $repo->getDomainsForTable($this->reseller->id, $dtRequest, $clientid);
        
        return $result;
    }
}
