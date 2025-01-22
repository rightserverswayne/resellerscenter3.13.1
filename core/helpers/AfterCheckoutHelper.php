<?php

namespace MGModule\ResellersCenter\Core\Helpers;

use MGModule\ResellersCenter\repository\whmcs\Hostings;
use MGModule\ResellersCenter\repository\whmcs\Domains;
use MGModule\ResellersCenter\repository\whmcs\HostingAddons;

/**
 * Class AfterCheckoutHelper
 * @author Michal Mendel <michal.me@modulesgarden.com>
 */
class AfterCheckoutHelper
{
    private $params;

    /**
     * AfterCheckoutHelper constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Function for changing Services Owner (WHMCS 8+ changes)
     * @param $newOwnerId
     * @throws \Exception
     */
    public function changeServicesOwner($newOwnerId)
    {
        if(!is_numeric($newOwnerId))
        {
            throw new \Exception('Wrong new service\'s owner ID in session');
        }

        $this->changeHostingOwner($newOwnerId);
        $this->changeDomainOwner($newOwnerId);
        $this->changeAddonOwner($newOwnerId);

    }

    /**
     * @param $newOwnerId
     */
    private function changeHostingOwner($newOwnerId)
    {
        if(!$this->params['ServiceIDs'])
        {
            return;
        }

        $hostings = new Hostings();
        foreach($this->params['ServiceIDs'] as $hostingId)
        {
            $hosting = $hostings->getHostingById($hostingId);
            $hosting->userid = $newOwnerId;
            $hosting->save();
        }

    }

    /**
     * @param $newOwnerId
     */
    private function changeDomainOwner($newOwnerId)
    {
        if(!$this->params['DomainIDs'])
        {
            return;
        }

        $domains = new Domains();
        foreach($this->params['DomainIDs'] as $domainId)
        {
            $domain = $domains->getById($domainId);
            $domain->userid = $newOwnerId;
            $domain->save();
        }
    }

    /**
     * @param $newOwnerId
     */
    private function changeAddonOwner($newOwnerId)
    {
        if(!$this->params['AddonIDs'])
        {
            return;
        }

        $hostingAddons = new HostingAddons();
        foreach($this->params['AddonIDs'] as $addonId)
        {
            $addonHosting = $hostingAddons->getById($addonId);
            $addonHosting->userid = $newOwnerId;
            $addonHosting->save();
        }
    }
}