<?php

namespace MGModule\ResellersCenter\repository;

use Illuminate\Database\Capsule\Manager as DB;
use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\libs\GlobalSearch\SearchTypes;
use MGModule\ResellersCenter\models\CreditLine;
use MGModule\ResellersCenter\models\Reseller as ResellerModel;
use MGModule\ResellersCenter\models\ResellerClient;
use MGModule\ResellersCenter\models\ResellerSetting;
use MGModule\ResellersCenter\models\whmcs\Client;
use MGModule\ResellersCenter\repository\source\AbstractRepository;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller as ResellerObj;

/**
 * Description of Resellers
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ResellersClients extends AbstractRepository
{
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\ResellerClient';
    }

    /**
     * Assign new Client to Reseller
     * There is no check if client is already assigned as reseller!
     *
     * @param type $resellerid
     * @param type $clientid
     * @since 3.0.0
     */
    public function createNew($resellerid, $clientid)
    {
        $newClient = $this->create(["client_id" => $clientid, "reseller_id" => $resellerid]);
        $resellerClientsSettingsRepo = new ResellersClientsSettings();
        $resellerClientsSettingsRepo->addDefaultSettingToResellerClient($newClient);
    }

    /**
     * Get all assigned clients to reseller.
     *
     * @param int $resellerid
     * @return array
     * @since 3.0.0
     */
    public function getByResellerId(?int $resellerid = null)
    {
        $query = DB::table("ResellersCenter_ResellersClients");
        $query->leftJoin("tblclients", function ($join) {
            $join->on("tblclients.id", "=", "ResellersCenter_ResellersClients.client_id");
        });

        if ($resellerid) {
            $query->where("reseller_id", $resellerid);
        }

        return $query->get();
    }

    /**
     * Get client relation
     *
     * @param $clientid
     * @param null $type
     * @param null $resellerid
     * @return mixed
     */
    public function getByRelid($clientid, $type = null, $resellerid = null)
    {
        $model = $this->getModel();
        if ($resellerid == null) {
            $result = $model->where("client_id", $clientid)->first();
        } else {
            $result = $model->where("client_id", $clientid)
                ->where("reseller_id", $resellerid)
                ->first();
        }

        return $result;
    }

    /**
     * Get client <-> reseller relations
     *
     * @param type $clientid
     */
    public function getByRelidAndResellerId($clientid, $resellerid)
    {
        $model  = $this->getModel();
        return $model->where("client_id", $clientid)->where("reseller_id", $resellerid)->first();
    }

    public function getResellerAsUserByClientId($clientId)
    {
        $relation = $this->getByRelid($clientId);
        if ($relation) {
            $tblClient = (new Client())->getTable();
            $tblReseller = (new ResellerModel())->getTable();
            return ResellerModel::leftJoin($tblClient, $tblClient.'.id', '=', $tblReseller.'.client_id')->where($tblReseller.'.id',$relation->reseller_id)->first();
        }
        return null;
    }

    public function getResellerByClientId($clientId)
    {
        $relation = $this->getByRelid($clientId);
        if ($relation) {
            return ResellerModel::where('id',$relation->reseller_id)->first();
        }
        return null;
    }

    public function getClientsWithReseller()
    {
        $tblClient = (new Client())->getTable();
        $tblReseller = (new ResellerModel())->getTable();
        $tblResCli = (new ResellerClient())->getTable();

        return ResellerClient::select(
            $tblResCli.'.client_id',
            $tblClient.'.id as reseller_id',
            DB::raw("IF({$tblClient}.companyname !='' AND {$tblClient}.companyname IS NOT NULL,
               {$tblClient}.companyname,
                CONCAT({$tblClient}.firstname,' ', {$tblClient}.lastname)) as resellerInfo"))
            ->join($tblReseller, $tblReseller.'.id', '=', $tblResCli.'.reseller_id')
            ->leftJoin($tblClient, $tblClient.'.id', '=', $tblReseller.'.client_id')
            ->get();
    }

    /**
     * Delete from Reseller
     *
     * @param type $clientid
     * @since 3.0.0
     */
    public function deleteByClientId($clientid)
    {
        $client = new ResellerClient();
        $client->where("client_id", $clientid)->delete();
    }

    /**
     * @param $limit
     * @param $search
     * @return array|\Illuminate\Database\Query\Builder[]
     */
    public function getAssigned($resellerid, $limit, $search)
    {
        $query = DB::table("ResellersCenter_ResellersClients");
        $query->leftJoin("tblclients", function ($join) {
            $join->on("tblclients.id", "=", "ResellersCenter_ResellersClients.client_id");
        });

        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->orWhere("ResellersCenter_ResellersClients.id", "LIKE", "%$search%")
                    ->orWhere("tblclients.firstname", "LIKE", "%$search%")
                    ->orWhere("tblclients.lastname", "LIKE", "%$search%")
                    ->orWhere("tblclients.companyname", "LIKE", "%$search%")
                    ->orWhere("tblclients.email", "LIKE", "%$search%");
            });
        }

        $query->where("ResellersCenter_ResellersClients.reseller_id", $resellerid);
        $query->select("tblclients.id", "tblclients.firstname", "tblclients.lastname", "tblclients.companyname", "tblclients.email");
        return $query->limit($limit)->get();
    }

    /**
     * This is special function to get data for datatable.
     * Use getAssigned for normal use
     *
     * @param int $resellerid
     * @param stdObj $dtRequest
     * @return array
     * @since 3.0.0
     */
    public function getAssignedForTable($resellerid, $dtRequest)
    {
        $creditLinesTable = (new CreditLine())->getTable();

        $query = DB::table("ResellersCenter_ResellersClients");

        $query->leftJoin("tblclients", function ($join) {
            $join->on("tblclients.id", "=", "ResellersCenter_ResellersClients.client_id");
        });

        $query->leftJoin("tblcurrencies", "tblcurrencies.id", "=", "tblclients.currency");
        $query->leftJoin($creditLinesTable, $creditLinesTable.".client_id", "=", "tblclients.id");

        $query->where("ResellersCenter_ResellersClients.reseller_id", $resellerid);

        $totalCount = $query->count();

        //Apply Filters
        $filter = $dtRequest->filter;
        if (!empty($filter)) {
            $query->where(function ($query) use ($filter) {
                $query->orWhere("ResellersCenter_ResellersClients.id", "LIKE", "%$filter%")
                    ->orWhere("tblclients.firstname", "LIKE", "%$filter%")
                    ->orWhere("tblclients.lastname", "LIKE", "%$filter%")
                    ->orWhere("tblclients.companyname", "LIKE", "%$filter%")
                    ->orWhere("ResellersCenter_ResellersClients.created_at", "LIKE", "%$filter%");
            });
        }
        $displayAmount = $query->count();

        $query->select(
            "ResellersCenter_ResellersClients.id",
            "ResellersCenter_ResellersClients.created_at",
            "tblclients.firstname",
            "tblclients.lastname",
            "tblclients.companyname",
            "tblcurrencies.prefix",
            "tblcurrencies.suffix",
            DB::raw("{$creditLinesTable}.usage as creditLineUsage"),
            DB::raw("{$creditLinesTable}.limit as creditLineLimit"),
            DB::raw("tblclients.id as client_id")
        );

        $query->groupBy("ResellersCenter_ResellersClients.id");
        $query->take($dtRequest->limit)->skip($dtRequest->offset);
        if ($dtRequest->columns[$dtRequest->orderBy] != 'income') {
            $query->orderBy($dtRequest->columns[$dtRequest->orderBy], $dtRequest->orderDir);
        }

        $data = collect($query->get())->toArray();

        //Add statistic
        foreach ($data as $client) {
            $client->income = $this->getIncomeFromClient($client->client_id);
        }

        //Extra sort
        if ($dtRequest->columns[$dtRequest->orderBy] == 'income') {
            usort($data, function ($a, $b) use ($dtRequest) {
                if ($dtRequest->orderDir == 'asc') {
                    return ($a->income < $b->income) ? -1 : 1;
                }
                else {
                    return ($a->income > $b->income) ? -1 : 1;
                }
            });
        }

        foreach ($data as $client) {
            $client->income = $client->prefix . $client->income . $client->suffix ;
            $client->creditLine = $client->creditLineLimit && $client->creditLineLimit != '0.00' ?
                $client->prefix . $client->creditLineUsage . $client->suffix . ' / ' .
                $client->prefix .$client->creditLineLimit . $client->suffix : '-';
        }

        return [
            "data"          => $data,
            "displayAmount" => $displayAmount,
            "totalAmount"   => $totalCount
        ];
    }

    public function getIncomeFromClient($clientid, $start = null, $end = null)
    {
        $income = 0;
        $model    = $this->getModel();
        $rcClient = $model->where("client_id", $clientid)->first();
        $reseller = new ResellerObj($rcClient->reseller);

        if (!$reseller->settings->admin->resellerInvoice) {
            //Get From WHMCS Invoices
        $query = DB::table("tblinvoices");
            $query->join("tblinvoiceitems", "tblinvoiceitems.invoiceid", "=", "tblinvoices.id");
            $query->join("ResellersCenter_ResellersProfits", "ResellersCenter_ResellersProfits.invoiceitem_id", "=", "tblinvoiceitems.id");

            if ($start) {
                $query->where("tblaccounts.date", ">", $start);
            }

            if ($end) {
                $query->where("tblaccounts.date", "<", $end);
            }

            $query->where("tblinvoices.userid", $clientid);
            $query->select(DB::raw("SUM(ResellersCenter_ResellersProfits.amount) as income"));
            $result = $query->first();
            $income += $result->income;
        }
        else {
            //Get from RCInvoices
            foreach ($rcClient->whmcsClient->resellerInvoices as $invoice) {
                if ($start !== null && $invoice->date < $start) {
                    continue;
                }

                if ($end !== null && $invoice->date > $end) {
                    continue;
                }

                if ($invoice->status == Invoices::STATUS_PAID) {
                    $reseller = Helper::calcCurrencyValue($invoice->total + $invoice->credit, $rcClient->whmcsClient->currency, $invoice->reseller->client->currency);
                    $admin    = $invoice->whmcsInvoice->total + $invoice->whmcsInvoice->credit;

                    $income += round($reseller - $admin, 2);
                }
            }
        }

        return $income;
    }

    public function getResellerIdByHisClientId($clientId)
    {
        $reseller = ResellerClient::where('client_id', $clientId)->first();
        return $reseller->reseller_id;
    }

    public function getResellerObjectByHisClientId($clientId)
    {
        $reseller = ResellerClient::where('client_id', $clientId)->first();
        return Reseller::createById($reseller->reseller_id);
    }

    public function getResellerClientsForGlobalSearch($resellerId, $filter)
    {
        $query = DB::table("ResellersCenter_ResellersClients");

        $query->select("ResellersCenter_ResellersClients.id")
            ->addSelect(DB::raw('"'.SearchTypes::CLIENT_TYPE.'" AS type'))
            ->addSelect(DB::raw("CONCAT(tblclients.firstname, ' ', tblclients.lastname) as name"))
            ->addSelect("tblclients.status")
            ->addSelect("tblclients.datecreated as date")
            ->addSelect("tblclients.id as client_id");

        $query->where('reseller_id', $resellerId);

        $query->where(function($query) use($filter)
        {
            $query->orWhere("tblclients.firstname", "LIKE", "%$filter%")
                ->orWhere("tblclients.lastname", "LIKE", "%$filter%")
                ->orWhere("tblclients.companyname", "LIKE", "%$filter%")
                ->orWhere("tblclients.email", "LIKE", "%$filter%")
                ->orWhere("tblclients.address1", "LIKE", "%$filter%")
                ->orWhere("tblclients.address2", "LIKE", "%$filter%")
                ->orWhere("tblclients.postcode", "LIKE", "%$filter%")
                ->orWhere("tblclients.city", "LIKE", "%$filter%")
                ->orWhere("tblclients.state", "LIKE", "%$filter%")
                ->orWhere("tblclients.phonenumber", "LIKE", "%$filter%")
                ->orWhere("ResellersCenter_ResellersClients.id", "LIKE", "%$filter%");
        });

        $query->leftJoin("tblclients", "tblclients.id", "=", "ResellersCenter_ResellersClients.client_id");

        $query->groupBy("ResellersCenter_ResellersClients.id");

        return $query;
    }

    public function getSettingsByClientId($clientId):array
    {
        $settings = [];
        $model    = $this->getModel();
        $rcClient = $model->where("client_id", $clientId)->first();
        $settingsRepo = new ResellersClientsSettings();

        $rcClientSettings = $settingsRepo->getClientSettings($rcClient);
        $resellerPrivateSettings = $rcClient->reseller->settings['private'];

        foreach ($settingsRepo->getRequiredSettings() as $setting) {
            $settingName = $setting->getName();
            $settingValue = array_key_exists($settingName, $rcClientSettings) ?
                $rcClientSettings[$settingName] :
                (array_key_exists($settingName, $resellerPrivateSettings) ?
                    $resellerPrivateSettings[$settingName]
                    : $setting->getDefaultValue());

            $settings[$settingName] = $settingValue;
        }
        return $settings;
    }
}
