<?php

namespace MGModule\ResellersCenter\libs\ResellerClientsCases;

use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\repository\whmcs\Hostings;
use \Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class ExistsCase extends AbstractCase
{
    public function getServicesIds()
    {
        $repo = new Hostings();

        return $repo->getAssignedIdsByUserId(Session::get("uid"));
    }

    protected function getAvailableInvoicesIds():array
    {
        return $this->availableIds ?: $this->invoicesRepo->getResellerRelatedClientInvoicesIds(Session::get('uid'));
    }

    abstract public function getAllOrdersActivatedByCreditLine($userId);
}