<?php

namespace MGModule\ResellersCenter\Repository\Source;

interface InvoiceRepoInterface
{
    public function getInvoicesForGlobalSearch($resellerId, $filter);
    public function getInvoiceItemsRepo();
}