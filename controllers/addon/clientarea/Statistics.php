<?php

namespace MGModule\ResellersCenter\controllers\addon\clientarea;

use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;

use MGModule\ResellersCenter\repository\Invoices as RCInvoicesRepo;
use MGModule\ResellersCenter\repository\Resellers;
use MGModule\ResellersCenter\repository\ResellersProfits;

use MGModule\ResellersCenter\repository\whmcs\Orders as OrdersRepo;
use MGModule\ResellersCenter\repository\whmcs\Clients as ClientsRepo;
use MGModule\ResellersCenter\repository\whmcs\Currencies;

use MGModule\ResellersCenter\core\datatable\DatatableDecorator as Datatable;
use MGModule\ResellersCenter\mgLibs\Lang;

use MGModule\ResellersCenter\core\helpers\ClientAreaHelper as CAHelper;
use MGModule\ResellersCenter\core\Request;

/**
 * Description of Statistics
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Statistics extends AbstractController
{
    public function indexHTML()
    {
        return ['tpl'   => 'base',
            'vars' => []];
    }
    
    /**
     * Get summarized sales for logged reseller
     * 
     * @return type
     */
    public function getSalesJSON()
    {
        $reseller = Reseller::getLogged();
        
        $startDate = new \DateTime(Request::get("startDate"));
        $endDate = new \DateTime(Request::get("endDate"));
        
        $counter = 0;
        $labels = [];
        $sales = [];
        while ($startDate <= $endDate) {
            $end = clone $startDate;
            $end->add(new \DateInterval("P1D"));
            
            //When reseller invoice option is selected
            if ($reseller->settings->admin->resellerInvoice) {
                $invoicesRepo = new RCInvoicesRepo();
                $invoices = $invoicesRepo->getByTime($startDate->format("Y-m-d"), $end->format("Y-m-d"),  $reseller->id);

                $total = 0;
                foreach ($invoices as $invoice) {
                    $amount = convertCurrency($invoice->subtotal, $invoice->client->currency, $reseller->client->currency);
                    $total += $amount - ($invoice->whmcsInvoice->total + $invoice->whmcsInvoice->credit);
                }
                
                $sales[Lang::T("sale")][] = $total;
            }
            else {
                $repo = new Resellers();
                $sales[Lang::T("sale")][] = $repo->getResellerSale($reseller->id, $startDate->format("Y-m-d"), $end->format("Y-m-d"));
            }
            
            $labels[$counter] = $startDate->format("Y-m-d");
            $counter++;
            $startDate->add(new \DateInterval("P1D"));
        }
        
        $currencies = new Currencies();
        $currency = $currencies->getDefault();
        
        return array("data" => $sales, "labels" => $labels, "currency" => $currency->prefix . " " . $currency->suffix);
    }
    
    /**
     * Get monthly income for logged reseller
     * 
     * @return type
     */
    public function getMonthlyDataJSON()
    {
        $reseller = Reseller::getLogged();

        $startDate = new \DateTime("first day of January");
        $endDate = new \DateTime("last day of December");
        
        $result = [];
        while ($startDate <= $endDate) {
            $end = clone $startDate;
            $end->add(new \DateInterval("P1M"));

            $month = $startDate->format("F");
            $result[$month]["totalsale"] = 0;
            $result[$month]["income"] = 0;
        
            //When reseller invoice option is selected
            if ($reseller->settings->admin->resellerInvoice) {
                $invoicesRepo = new RCInvoicesRepo();
                $invoices = $invoicesRepo->getByTime($startDate->format("Y-m-d"), $end->format("Y-m-d"), $reseller->id);
                foreach ($invoices as $invoice) {
                    $amount = convertCurrency($invoice->subtotal, $invoice->client->currency, $reseller->client->currency);
                    $result[$month]["income"] += $amount - ($invoice->whmcsInvoice->total + $invoice->whmcsInvoice->credit);
                }
            }
            else {
                $profitsRepo = new ResellersProfits();
                $profits = $profitsRepo->getProfitsByReseller($reseller->id, $startDate->format("Y-m-d"), $end->format("Y-m-d"));
                foreach ($profits as $profit) {
                    $result[$month]["income"] += $profit->amount;
                }
            }

            $startDate->add(new \DateInterval("P1M"));
        }
        
        return $result;
    }
    
    public function getClientsStatisticTableJSON()
    {

        $reseller = Reseller::getLogged();
        $result = $this->getRows($reseller);

        $datatable = new Datatable();
        $datatable->parseData($result, count($reseller->assignedClients), count($reseller->assignedClients));

        return $datatable->getResult();
    }

    private function getRows($reseller)
    {
        $clientsRepo = new ClientsRepo();
        $ordersRepo = new OrdersRepo();
        $dtRequest = Request::getDatatableRequest();
        $result = [];

        foreach ($reseller->assignedClients as $key => $assignedClient) {
            if ($key < $dtRequest->offset) {
                continue;
            }
            if ($key >= $dtRequest->limit + $dtRequest->offset) {
                break;
            }

            $client = $clientsRepo->find($assignedClient->client_id);
            $result[$key]["id"] = $client->id;
            $result[$key]["name"] = $client->firstname . " " . $client->lastname;
            $result[$key]["value"] = 0;
            $result[$key]["income"] = 0;

            $orders = $ordersRepo->getRelated($reseller->id, $client->id);
            $result[$key]["orders"] = count($orders);
            foreach ($orders as $order) {
                $orderObj = $ordersRepo->find($order->id);
                if ($reseller->settings->admin->resellerInvoice) {
                    $result[$key]["value"] += (float)convertCurrency($order->amount, $orderObj->invoice->resellerInvoice->client->currency, $reseller->client->currency);
                    if ($orderObj->invoice->resellerInvoice->status == RCInvoicesRepo::STATUS_PAID) {
                        $amount = convertCurrency($orderObj->invoice->resellerInvoice->subtotal, $orderObj->invoice->resellerInvoice->client->currency, $reseller->client->currency);
                        $result[$key]["income"] = (float)$result[$key]["income"] + (float)$amount - ($orderObj->invoice->total + $orderObj->invoice->credit);
                    }
                }
                else {
                    $result[$key]["value"] = (float)$result[$key]["value"] + (float)convertCurrency($order->amount, $orderObj->invoice->client->currency, $reseller->client->currency);
                    foreach ($orderObj->invoice->items as $item) {
                        $result[$key]["income"] = (float)$result[$key]["income"] + (float)$item->profit->amount;
                    }
                }
            }
            $currencyPrefix = $assignedClient->whmcsClient->currencyObj()->first()->prefix;
            $currencySuffix = $assignedClient->whmcsClient->currencyObj()->first()->suffix;

            $result[$key]["income"] = $currencyPrefix . $result[$key]["income"] . $currencySuffix;
            $result[$key]["value"] = $currencyPrefix . $result[$key]["value"] . $currencySuffix;
        }

        return $this->orderBy($result, $dtRequest);
    }

    private function orderBy($data, $dtRequest)
    {
        usort($data, function($obA, $obB) use($dtRequest) 
        {
            $orderBy = $dtRequest->columns[$dtRequest->orderBy];
            
            if($dtRequest->orderDir == 'asc'){
                return strnatcmp($obA[$orderBy], $obB[$orderBy]);
            }
            else {
                return strnatcmp($obB[$orderBy], $obA[$orderBy]);
            }
        });
        
        return $data;
    }
    
}