<?php

namespace MGModule\ResellersCenter\repository;

use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\models\ResellerClient;
use MGModule\ResellersCenter\models\ResellerService;
use MGModule\ResellersCenter\models\source\ModelException;
use MGModule\ResellersCenter\models\whmcs\Transaction;
use MGModule\ResellersCenter\repository\source\AbstractRepository;

use MGModule\ResellersCenter\models\Reseller;
use \Illuminate\Database\Capsule\Manager as DB;
use MGModule\ResellersCenter\repository\whmcs\Currencies;


/**
 * Description of Resellers
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Resellers extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\Reseller';
    }

    /**
     * Assign WHMCS client to act as Reseller
     *
     * @param $clientid
     * @param $groupid
     * @return Reseller
     * @throws ModelException
     */
    public function createNew($clientid, $groupid)
    {
        //Make sure that admin has provided client id
        if (empty($clientid)) {
            throw new ModelException("invalid_clientid");
        }

        //Make sure that admin has provided group id for new reseller
        if (empty($groupid)) {
            throw new ModelException("invalid_groupid");
        }

        //Insert reseller model to database
        $model = Reseller::create(["client_id" => $clientid, "group_id" => $groupid]);

        return $model;
    }
    
    /**
     * Delete reseller with relations
     * 
     * @since 3.1.0
     * @param type $resellerid
     */
    public function deleteWithRelations($resellerid)
    {
        $reseller = $this->getModel()->find($resellerid);
        $this->deleteResellerClients($reseller->assignedClients->pluck('id'));
        $this->deleteResellerServices($reseller->services->pluck('id'));

        \WHMCS\Database\Capsule::table('ResellersCenter_ResellersSettings')->where('reseller_id', $reseller->id)->delete();

        $reseller->delete();
    }

    protected function deleteResellerClients($clientIds)
    {
        ResellerClient::whereIn('id',$clientIds)->delete();
    }

    protected function deleteResellerServices($servicesIds)
    {
        ResellerService::whereIn('id',$servicesIds)->delete();
    }
    
    /**
     * Transfer Reseller to different group
     * 
     * @since 3.0.0
     * @param type $resellerid
     * @param type $groupid
     */
    public function updateGroup($resellerid, $groupid)
    {
        $reseller = new Reseller();
        $reseller->where("id", $resellerid)
                 ->update(array("group_id" => $groupid));
    }

    /**
     * Get number of reseller assinged to group
     * 
     * @since 3.0.0
     * @param type $groupid
     * @return type
     */
    public function getResellersNoByGroupId($groupid)
    {
        $query = DB::table("ResellersCenter_Resellers");
        $query->where("group_id","=",$groupid);
        
        return $query->count();
    }
    
    /**
     * Find reseller by client id
     * 
     * @since 3.0.0
     * @param int $clientid
     */
    public function getResellerByClientId($clientId)
    {
        $model = new Reseller();
        return $model->findByClientId($clientId);
    }
    
    /**
     * Get Reseller by his domain
     * 
     * @param string $domain
     * @return type
     */
    public function getResellerByDomainName($domain)
    {
        if (empty($domain)) {
            return new Reseller();
        }

        $domain = Server::getDomainWithoutWwwPrefix($domain);
        
        $query = DB::table("ResellersCenter_Resellers");
        $query->leftjoin('ResellersCenter_ResellersSettings', function($join) {
                $join->on("ResellersCenter_ResellersSettings.reseller_id", "=", DB::raw("ResellersCenter_Resellers.id"));
            });
        
        $query->where("ResellersCenter_ResellersSettings.setting", "domain");
        $query->where("ResellersCenter_ResellersSettings.value", $domain);
        $res = $query->first();
        
        //Load Reseller model
        $model = new Reseller();
        return $model->find($res->id);
    }
    
    /**
     * Get number of clients related with reseller
     * 
     * @since 3.0.0
     * @param type $dtRequest
     * @return type
     */
    public function getNumberOfClientsForTable($dtRequest)
    {
        $query = DB::table("ResellersCenter_Resellers");
        $query->leftJoin("tblclients", "tblclients.id", "=", "ResellersCenter_Resellers.client_id");
        $query->leftJoin("ResellersCenter_ResellersClients", "ResellersCenter_ResellersClients.reseller_id", "=", "ResellersCenter_Resellers.id");
        
        $query->select(
            DB::raw("CONCAT(tblclients.firstname, ' ', tblclients.lastname) as reseller"),
            DB::raw("ifnull(COUNT(ResellersCenter_ResellersClients.id), 0) as clients")
        );
        
        $query->groupBy("ResellersCenter_Resellers.id");
        $query->orderBy($dtRequest->columns[$dtRequest->orderBy], $dtRequest->orderDir);
        $result = $query->get();
        
        $amount = DB::table("ResellersCenter_Resellers")->count();
        return array("data" => $result,  "displayAmount" => $amount, "totalAmount" => $amount);
    }
    
    /**
     * Get Data for data table.
     * 
     * @since 3.0.0
     * @param type $dtRequest
     * @return type
     */
    public function getDataForTable($dtRequest)
    {
        $query = DB::table("ResellersCenter_Resellers");
        
        $query->leftjoin('ResellersCenter_Groups', function($join) {
                $join->on("ResellersCenter_Groups.id", "=", DB::raw("ResellersCenter_Resellers.group_id"));
            });
        $query->leftjoin('tblclients', function($join) {
                $join->on("tblclients.id", "=", DB::raw("ResellersCenter_Resellers.client_id"));
            });
        $query->leftjoin('ResellersCenter_ResellersSettings', function($join) {
                $join->on("ResellersCenter_Resellers.id", "=", DB::raw("ResellersCenter_ResellersSettings.reseller_id"));
                $join->where("ResellersCenter_ResellersSettings.setting", "=", "status");
            });
        $query->leftJoin('ResellersCenter_CreditLine', "ResellersCenter_CreditLine.client_id", "tblclients.id");
        
        //Apply global search
        $filter = $dtRequest->filter;
        if (!empty($filter)) {
            $query->where(function($query) use ($filter)
            {
                $filter = DB::getPdo()->quote("%{$filter}%");
                $query->where("ResellersCenter_Groups.name", "LIKE", $filter)
                      ->orWhere("tblclients.firstname", "LIKE", $filter)
                      ->orWhere("tblclients.lastname", "LIKE", $filter)
                      ->orWhere("tblclients.companyname", "LIKE", $filter)
                      ->orWhere(DB::raw("DATE_FORMAT(ResellersCenter_Resellers.created_at, '%Y-%m-%d')"), "LIKE", $filter);
            });
        }

        $query->select(
            "ResellersCenter_Resellers.id",
            "ResellersCenter_Groups.name as groupname", "ResellersCenter_Groups.id as group_id", 
            "tblclients.id as client_id", "tblclients.firstname", "tblclients.lastname", "tblclients.companyname",
            DB::raw("ifnull(ResellersCenter_ResellersSettings.value, 'off') as status"),
            "ResellersCenter_Resellers.created_at",
            DB::raw("ResellersCenter_CreditLine.usage as creditlineusage"),
            DB::raw("ResellersCenter_CreditLine.limit as creditlinelimit"),
        );

        $displayAmount = $query->count();
        
        $orderCol = $dtRequest->columns[$dtRequest->orderBy];
        if ($orderCol != 'totalsale' && $orderCol != 'monthsale') {
            $query->orderBy($dtRequest->columns[$dtRequest->orderBy], $dtRequest->orderDir);
        }   
        $query->take($dtRequest->limit)->skip($dtRequest->offset);

        $data = collect($query->get())->toArray();

        $currencies = new Currencies();
        $currency = $currencies->getDefault();
                
        foreach ($data as $reseller) {
            $reseller->totalsale = number_format($this->getResellerSale($reseller->id), 2);
            $reseller->monthsale = number_format($this->getResellerSale($reseller->id, date("Y-m-1", strtotime("-1 month")), date("Y-m-t", strtotime("-1 month"))), 2);
            $reseller->creditline = $reseller->creditlinelimit && $reseller->creditlinelimit != '0.00'  ?
                $currency->prefix . $reseller->creditlineusage . $currency->suffix . ' / ' . $currency->prefix . $reseller->creditlinelimit . $currency->suffix : '-';
        }
        
        //Extra sort
        if ($orderCol == 'totalsale' || $orderCol == 'monthsale') {
            usort($data,function($a, $b) use ($dtRequest, $orderCol) 
            {
                if ($dtRequest->orderDir == 'asc') {
                    return ($a->{$orderCol} < $b->{$orderCol}) ? -1 : 1;
                }
                else {
                    return ($a->{$orderCol} > $b->{$orderCol}) ? -1 : 1;
                }
            }); 
        }
        
        return array(
            "data" => $data,
            "displayAmount" => $displayAmount,
            "totalAmount" => DB::table("ResellersCenter_Resellers")->count()
        );
    }
    
    public function getResellerIncomesTable($dtRequest)
    {
        $query = DB::table("ResellersCenter_Resellers");
        $query->leftJoin("tblclients", "tblclients.id", "=", "ResellersCenter_Resellers.client_id");
        
        $query->leftJoin("ResellersCenter_Invoices", "ResellersCenter_Resellers.id", "=", "ResellersCenter_Invoices.reseller_id");
        $query->leftJoin("tblinvoices", "tblinvoices.id", "=", "ResellersCenter_Invoices.relinvoice_id");

        $query->leftJoin("ResellersCenter_ResellersProfits", "ResellersCenter_ResellersProfits.reseller_id", "=", "ResellersCenter_Resellers.id");
        $query->leftJoin("tblinvoiceitems", "tblinvoiceitems.id", "=", "ResellersCenter_ResellersProfits.invoiceitem_id");
        $query->leftJoin("tblinvoices as whmcsinvoices", "whmcsinvoices.id", "=", "tblinvoiceitems.invoiceid");
                
        $query->select("ResellersCenter_Resellers.id")
              ->addSelect(DB::raw("CONCAT(tblclients.firstname, ' ', tblclients.lastname) as name"))
              ->addSelect(DB::raw("IFNULL(SUM(ResellersCenter_Invoices.subtotal), 0) + IFNULL(SUM(whmcsinvoices.subtotal), 0) as totalsale"))
              ->addSelect(DB::raw("IFNULL(SUM(ResellersCenter_Invoices.subtotal - tblinvoices.subtotal), 0) + IFNULL(SUM(ResellersCenter_ResellersProfits.amount), 0) as resellerincome"))
              ->addSelect(DB::raw("IFNULL((SUM(ResellersCenter_Invoices.subtotal) - SUM(ResellersCenter_Invoices.subtotal - tblinvoices.subtotal)), 0) + IFNULL((SUM(whmcsinvoices.subtotal) - SUM(ResellersCenter_ResellersProfits.amount)), 0) as income"));
        
        
        $query->orderBy($dtRequest->columns[$dtRequest->orderBy], $dtRequest->orderDir);
        $query->groupBy("ResellersCenter_Resellers.id");
        $result = $query->get();

        return $result;
    }
    
    /**
     * Get reseller sale
     * 
     * @param $resellerid
     * @reutrn type
     */
    public function getResellerSale($resellerid, $start = null, $end = null)
    {
        $transactions = [];
        $reseller = $this->getModel()->find($resellerid);
        $invoiceItemIds = $reseller->profits()
                                   ->pluck('invoiceitem_id')
                                   ->toArray();

        if ($invoiceItemIds) {
            $transactions = Transaction::select('tblaccounts.id', 'tblaccounts.date', 'tblaccounts.amountin', 'tblaccounts.fees', 'tblaccounts.amountout', 'tblaccounts.rate')
                ->distinct()
                ->join('tblinvoices', 'tblinvoices.id','=','tblaccounts.invoiceid')
                ->join('tblinvoiceitems', function ($join) use ($invoiceItemIds) {
                    $join->on('tblinvoiceitems.invoiceid', '=', 'tblinvoices.id')
                         ->whereIn('tblinvoiceitems.id', $invoiceItemIds);
                })
                ->get();
        }

        foreach ( $transactions as $transaction ) {
            if ( $start && $transaction->date < $start ) {
                continue;
            }

            if ( $end && $transaction->date > $end ) {
                continue;
            }
            $totalsale += round(($transaction->amountin - $transaction->fees - $transaction->amountout) * 1 / $transaction->rate, 2);
        }
        
        //Get from RCInvoices
        foreach ($reseller->RCInvoices as $invoice) {
            if ($start !== null && $invoice->date < $start) {
                continue;
            }
            
            if ($end !== null && $invoice->date > $end) {
                continue;
            }
            
            if ($invoice->status == Invoices::STATUS_PAID) {
                $amount = ($invoice->total + $invoice->credit) - ($invoice->whmcsInvoice->total + $invoice->whmcsInvoice->credit);
                $income += convertCurrency($amount, $invoice->client->currency, $reseller->client->currency);
            }
        }
        
        return $totalsale + $income;
    }
}