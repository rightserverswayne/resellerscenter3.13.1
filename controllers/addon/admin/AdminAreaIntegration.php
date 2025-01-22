<?php

namespace MGModule\ResellersCenter\Controllers\Addon\Admin;

use MGModule\ResellersCenter\mgLibs\process\AbstractController;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\repository\ResellersServices;

class AdminAreaIntegration extends AbstractController
{
    private $resellersRepository;
    private $clientsRepository;

    public function __construct($input = [])
    {
        $this->resellersRepository = new ResellersServices();
        $this->clientsRepository = new ResellersClients();
        parent::__construct($input);
    }

    public function getClientsWithResellerJSON()
    {
        return $this->clientsRepository->getClientsWithReseller();
    }

    public function getProductsWithResellerJSON()
    {
        return $this->resellersRepository->getServicesWithResellerByType(ResellersServices::TYPE_HOSTING);
    }

    public function getDomainsWithResellerJSON()
    {
        return $this->resellersRepository->getServicesWithResellerByType(ResellersServices::TYPE_DOMAIN);
    }

    public function getAddonsWithResellerJSON()
    {
        return $this->resellersRepository->getServicesWithResellerByType(ResellersServices::TYPE_ADDON);
    }
}