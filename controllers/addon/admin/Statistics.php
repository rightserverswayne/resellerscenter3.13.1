<?php
namespace MGModule\ResellersCenter\Controllers\Addon\Admin;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;

use MGModule\ResellersCenter\repository\Invoices;
use MGModule\ResellersCenter\repository\whmcs\Transactions;
use MGModule\ResellersCenter\repository\whmcs\Currencies;

use MGModule\ResellersCenter\repository\ResellersProfits;
use MGModule\ResellersCenter\repository\Resellers as ResellersRepo;
use MGModule\ResellersCenter\repository\Groups as GroupsRepo;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\core\datatable\DatatableDecorator as Datatable;

use MGModule\ResellersCenter\mgLibs\Lang;
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
        return array(
            'tpl'   => 'base',
            'vars' => array()
        );
    }
       
    /**
     * Get resellers for select2
     * 
     * @return type
     */
    public function getResellersJSON()
    {
        $search = Request::get("term");
        
        $repo = new ResellersRepo();
        $model = $repo->getModel();
        $resellers = $model->withFilter($search)->get();

        $result = array();
        foreach($resellers as $reseller) 
        {
            $result[] = array(
                "id" => $reseller->id,
                "name" => $reseller->client->firstname . " " . $reseller->client->lastname,
                "groupname" => $reseller->group->name
            );
        }
        
        return $result;
    }
    
    public function getSalesJSON()
    {
        $resellers = empty(Request::get("resellers")) ? $this->getDefaultResellers() : Request::get("resellers");

        $labels = array();
        $sales = array();
        $repo = new ResellersRepo();
        foreach($resellers as $resellerid)
        {
            $reseller = new Reseller($resellerid);
            $startDate = new \DateTime(Request::get("startDate"));
            $endDate = new \DateTime(Request::get("endDate"));
            
            $counter = 0;
            while($startDate <= $endDate)
            {
                $end = clone $startDate;
                $end->add(new \DateInterval("P1D"));
                
                $key = "#{$reseller->id} {$reseller->client->firstname} {$reseller->client->lastname}";

                if($reseller->settings->admin->resellerInvoice)
                {
                    $invoicesRepo = new Invoices();
                    $invoices = $invoicesRepo->getByTime($startDate->format("Y-m-d"), $end->format("Y-m-d"), $reseller->id);

                    $total = 0;
                    foreach($invoices as $invoice)
                    {
                        $total += $invoice->subtotal - ($invoice->whmcsInvoice->total + $invoice->whmcsInvoice->credit);
                    }

                    $sales[$key][] = round($total,2);
                }
                else
                {
                    $sales[$key][] = $repo->getResellerSale($reseller->id, $startDate->format("Y-m-d"), $end->format("Y-m-d"));
                }

                $labels[$counter] = $startDate->format("Y-m-d");
                $counter++;

                $startDate->add(new \DateInterval("P1D"));
            }
        }

        $currencies = new Currencies();
        $currency = $currencies->getDefault();
        
        return array("data" => $sales, "labels" => $labels, "currency" => $currency->prefix . " " . $currency->suffix);
    }
    
    /**
     * Get reseller's clients
     * 
     * @return $resellers
     */
    public function getClientsJSON()
    {
        $repo = new ResellersRepo();
        $resellers = $repo->all();
        
        $result = array("labels" => array(), "data" => array());
        foreach($resellers as $reseller)
        {
            $result["labels"][] = "#" . $reseller->id . " " . $reseller->client->firstname . " " . $reseller->client->lastname;
            $result["data"][] = count($reseller->assignedClients);
        }
        
        return $result;
    }
    
    public function getClientsTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();
        
        $repo = new ResellersRepo();
        $result = $repo->getNumberOfClientsForTable($dtRequest);
        
        $datatable = new Datatable();
        $datatable->parseData($result["data"]);

        return $datatable->getResult();
    }
    
    /**
     * Get monthly income for each month from all resellers
     * include total sale, reseller income nad admin income
     * 
     * @return type
     */
    public function getMonthlyDataJSON()
    {
        $repo = new ResellersRepo();
        $resellers = $repo->all();

        //Begining and End of the current year
        $startDate = new \DateTime("first day of January");
        $endDate = new \DateTime("last day of December");
        
        $result = array();
        while($startDate <= $endDate)
        {
            $end = clone $startDate;
            $end->add(new \DateInterval("P1M"));

            $month = Lang::absoluteT("months", $startDate->format("F"));
            $result[$month]["totalsale"] = 0;
            $result[$month]["resellersincome"] = 0;
            foreach($resellers as $model)
            {
                $reseller = new Reseller($model->id);

                if($reseller->settings->admin->resellerInvoice)
                {
                    $invoicesRepo = new Invoices();
                    $invoices = $invoicesRepo->getByTime($startDate->format("Y-m-d"), $end->format("Y-m-d"), $reseller->id);
                    foreach($invoices as $invoice)
                    {
                        $total = $invoice->total + $invoice->credit;
                        $result[$month]["totalsale"] += round($total ,2);
                        $result[$month]["resellersincome"] += round($total - ($invoice->whmcsInvoice->total + $invoice->whmcsInvoice->credit),2);
                    }
                }
                else
                {
                    $transactionsRepo = new Transactions();
                    $transactions = $transactionsRepo->getTransactionsByReseller($reseller->id, $startDate->format("Y-m-d"), $end->format("Y-m-d"));
                    foreach($transactions as $transaction) {
                        $result[$month]["totalsale"] += round(($transaction->amountin - $transaction->fees) * $transaction->rate, 2);
                    }

                    $profitsRepo = new ResellersProfits();
                    $profits = $profitsRepo->getProfitsByReseller($reseller->id, $startDate->format("Y-m-d"), $end->format("Y-m-d"));
                    foreach($profits as $profit) {
                        $result[$month]["resellersincome"] += round($profit->amount,2);
                    }                    
                }
                
                $result[$month]["income"] =round( $result[$month]["totalsale"] - $result[$month]["resellersincome"],2);
            }
            
            $startDate->add(new \DateInterval("P1M"));
        }
        
        return $result;
    }
    
    /**
     * Get incomes table data
     * All data is in default WHMCS currency!
     * 
     * @return type
     */
    public function getIncomesTableJSON()
    {               
        $dtRequest = Request::getDatatableRequest();
        $repo = new ResellersRepo();
        $result = $repo->getResellerIncomesTable($dtRequest);
        
        $datatable = new Datatable();
        $datatable->parseData($result, count($result), count($result));

        return $datatable->getResult();
    }
    
    /**
     * Get Resellers from the frist group.
     * If group is empty take next one.
     * 
     * @return array
     */
    private function getDefaultResellers()
    {
        $repo = new GroupsRepo();
        $groups = $repo->all();
        
        $resellers = array();
        foreach($groups as $group) 
        {
            if(!empty($group->resellers)) 
            {
                $resellers = $group->resellers;
                break;
            }
        }
        
        $result = array();
        foreach($resellers as $reseller) {
            $result[] = $reseller->id;
        }
        
        return $result;
    }
}