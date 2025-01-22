<?php

namespace MGModule\ResellersCenter\libs\ResellerClientsCases;

use MGModule\ResellersCenter\Addon;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\repository\whmcs\Hostings;

abstract class NoExistsCase extends AbstractCase
{
    public function getServicesIds()
    {
        $repo = new Hostings();
        return $repo->getNotAssignedIdsByUserId(Session::get("uid"));
    }

    public function getInvoicesFromWhmcsInvoices($invoices)
    {
        if (!Addon::I()->configuration()->adminStoreServiceFilter) {
            return $invoices;
        }

        $availableIds = $this->getAvailableInvoicesIds();
        $invoicesList = $invoices ?: [];

        return array_filter($invoicesList, function ($invoice) use ($availableIds){
            return in_array($invoice['id'], $availableIds);
        });
    }

    public function getInvoicesCounters()
    {
        $query = $this->invoicesRepo->getResellerNoRelatedClientInvoicesQuery(Session::get('uid'));
        return $this->invoicesRepo->getInvoicesCountersFromQuery($query);
    }

    public function getUnpaidInvoicesOverdueCount()
    {
        $query = $this->invoicesRepo->getResellerNoRelatedClientInvoicesQuery(Session::get('uid'));
        return $this->invoicesRepo->getUnpaidInvoicesOverdueCountFromQuery($query);
    }

    protected function getAvailableInvoicesIds():array
    {
        return $this->availableIds ?: $this->invoicesRepo->getResellerNoRelatedClientInvoicesIds(Session::get('uid'));
    }
}