<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Relations;
use MGModule\ResellersCenter\repository\ResellersServices;

/**
 * Description of Hosting
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Hosting extends AbstractRelations
{
    protected function getRepo()
    {
        return new ResellersServices();
    }
    
    public function getByClient($clientid)
    {
        $repo = $this->getRepo();
        $result = $repo->getServicesByClientId($clientid, ResellersServices::TYPE_HOSTING, $this->reseller->id);

        return $result;
    }
    
    public function getForTable($dtRequest, $clientid = null)
    {
        $isResellerInvoice = $this->reseller->settings->admin->resellerInvoice;

        $repo = $this->getRepo();
        return $repo->getHostingForTable($this->reseller->id, $dtRequest, $clientid, $isResellerInvoice);
    }
}
