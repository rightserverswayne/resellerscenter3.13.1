<?php

namespace MGModule\ResellersCenter\collections;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\repository\ResellersServices;
use MGModule\ResellersCenter\repository\ResellersTickets;
use MGModule\ResellersCenter\repository\Invoices as RcInvoices;
use MGModule\ResellersCenter\Repository\Source\InvoiceRepoInterface;
use MGModule\ResellersCenter\repository\whmcs\Orders;
use MGModule\ResellersCenter\repository\whmcs\Invoices as WhmcsInvoices;

class SearchCollection
{
    public function getGlobalSearchResult($resellerId, $filter, $dtRequest)
    {
        $ordersRepo = new Orders();
        $resultOrders = $ordersRepo->getResellerOrdersForGlobalSearch($resellerId, $filter);

        $resellersClientRepo = new ResellersClients();
        $resultClients = $resellersClientRepo->getResellerClientsForGlobalSearch($resellerId, $filter);

        $servicesRepo = new ResellersServices();
        $resultProduct = $servicesRepo->getHostingProductsForGlobalSearch($resellerId, $filter);
        $resultAddons = $servicesRepo->getHostingAddonsForGlobalSearch($resellerId, $filter);
        $resultDomains = $servicesRepo->getHostingDomainsForGlobalSearch($resellerId, $filter);

        $ticketsRepo = new ResellersTickets();
        $resultTickets = $ticketsRepo->getTicketsForGlobalSearch($resellerId, $filter);

        $invoicesRepo = $this->getInvoiceRepoByResellerId($resellerId);

        $resultInvoices = $invoicesRepo->getInvoicesForGlobalSearch($resellerId, $filter);

        $query = $resultOrders
            ->union($resultClients)
            ->union($resultProduct)
            ->union($resultAddons)
            ->union($resultDomains)
            ->union($resultTickets)
            ->union($resultInvoices);

        $totalCount = $query->count();
        $displayAmount = $totalCount;

        $query->take($dtRequest->limit)->skip($dtRequest->offset);
        $query->orderBy($dtRequest->columns[$dtRequest->orderBy], $dtRequest->orderDir);

        $result = $query->get();

        return ["data" => $result, "displayAmount" => $displayAmount,"totalAmount" => $totalCount];
    }

    protected function getInvoiceRepoByResellerId($resellerId): InvoiceRepoInterface
    {
        $reseller = Reseller::createById($resellerId);
        return $reseller->settings->admin->resellerInvoice ? new RcInvoices() : new WhmcsInvoices();
    }

}