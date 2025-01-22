<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Relations;
use MGModule\ResellersCenter\Core\EventManager;
use MGModule\ResellersCenter\repository\Invoices;
use \MGModule\ResellersCenter\repository\ResellersClients;

/**
 * Description of Clients
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Clients extends AbstractRelations
{
    /**
     * Define type ambiguous type.
     * If true then relation can belong to many Resellers
     *
     * @var boolean
     */
    protected $ambiguous = false;

    /**
     * @return ResellersClients
     */
    protected function getRepo()
    {
        return new ResellersClients();
    }

    /**
     * Assign client and add call ClientAssigned
     *
     * @param int $relid
     * @throws \Exception
     */
    public function assign($relid)
    {
        parent::assign($relid);
        EventManager::call("clientAssigned", $relid, $this->reseller->id);
    }

    /**
     * Get Assigned clients
     *
     * @param $limit
     * @param null $search
     * @return array|\Illuminate\Database\Query\Builder[]
     */
    public function getAssigned($limit, $search = null)
    {
        $repo = $this->getRepo();
        $result = $repo->getAssigned($this->reseller->id, $limit, $search);

        return $result;
    }

    /**
     * Unassign Client all related relations
     *
     * @param int $relid
     * @throws \Exception
     */
    public function unassign($relid)
    {
        $hosting = $this->reseller->hosting->getByClient($relid);
        foreach($hosting as $single)
        {
            $this->reseller->hosting->unassign($single->relid);
        }

        $addons = $this->reseller->addons->getByClient($relid);
        foreach($addons as $single)
        {
            $this->reseller->addons->unassign($single->relid);
        }

        $domains = $this->reseller->domains->getByClient($relid);
        foreach($domains as $single)
        {
            $this->reseller->domains->unassign($single->relid);
        }

        $repo = new Invoices();
        $invoices = $repo->getByClientAndStatus($relid);
        foreach($invoices as $invoice)
        {
            $invoice->delete();
        }

        parent::unassign($relid);
    }

    public function find($relid)
    {
        $repo = $this->getRepo();
        $result = $repo->getByRelId($relid);

        return $result;
    }
}