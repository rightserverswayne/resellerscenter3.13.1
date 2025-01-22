<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Relations;
use MGModule\ResellersCenter\repository\ResellersServices;

/**
 * Description of Addons
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Addons extends AbstractRelations
{
    protected function getRepo()
    {
        return new ResellersServices();
    }
    
    public function getByClient($clientid)
    {
        $repo = $this->getRepo();
        $result = $repo->getServicesByClientId($clientid, ResellersServices::TYPE_ADDON, $this->reseller->id);

        return $result;
    }
    
    public function getForTable($dtRequest, $clientid = null)
    {
        $isResellerInvoice = $this->reseller->settings->admin->resellerInvoice;

        $repo = $this->getRepo();
        $result = $repo->getAddonsForTable($this->reseller->id, $dtRequest, $clientid, $isResellerInvoice);
        
        return $result;
    }
}
