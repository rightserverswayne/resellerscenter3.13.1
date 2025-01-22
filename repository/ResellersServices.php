<?php
namespace MGModule\ResellersCenter\repository;

use MGModule\ResellersCenter\Core\Helpers\DataTable;
use MGModule\ResellersCenter\libs\GlobalSearch\SearchTypes;
use MGModule\ResellersCenter\models\Reseller;
use MGModule\ResellersCenter\models\ResellerService;
use MGModule\ResellersCenter\models\whmcs\Addon;
use MGModule\ResellersCenter\models\whmcs\Client;
use MGModule\ResellersCenter\models\whmcs\Domain;
use MGModule\ResellersCenter\models\whmcs\Hosting;
use MGModule\ResellersCenter\models\whmcs\HostingAddon;
use MGModule\ResellersCenter\models\whmcs\Product;
use MGModule\ResellersCenter\repository\source\AbstractRepository;

use \MGModule\ResellersCenter\repository\whmcs\InvoiceItems as WhmcsInvoiceItems;

use \Illuminate\Database\Capsule\Manager as DB;
use MGModule\ResellersCenter\repository\whmcs\Pricing;

/**
 * Description of ResellersServices
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ResellersServices extends AbstractRepository 
{
    const TYPES = array("hosting", "addon", "domain");
    const TYPE_HOSTING  = 'hosting';
    const TYPE_ADDON    = 'addon';
    const TYPE_DOMAIN   = 'domain';
    
    public function determinateModel() 
    {
        return 'MGModule\ResellersCenter\models\ResellerService';
    }
    
    /**
     * Assign service (hosting/addon/domain) to Reseller
     * 
     * @since 3.0.0
     * @param type $resellerid
     * @param type $relid
     * @param type $type
     */
    public function createNew($resellerid, $relid, $type)
    {
        $ra = new ResellerService();
        $ra->fillData($resellerid, $relid, $type);
        
        $ra->save();
    }

    /**
     * Get relations
     *
     * @param $relid
     * @param $type
     * @param null $resellerid
     * @return mixed
     */
    public function getByRelId($relid, $type, $resellerid = null)
    {
        $model = $this->getModel();
        if ($resellerid == null) {
            $result = $model->where("relid", $relid)->where("type", $type)->first();
        } else {
            $result = $model->where("relid", $relid)->where("type", $type)
                            ->where("reseller_id", $resellerid)->first();
        }

        return $result;
    }

    public function serviceBelongsToReseller($relId, $resellerId): bool
    {
        $model = $this->getModel();
        return (bool) $model->where("relid", $relId)->where("reseller_id", $resellerId)->first();
    }
    
    public function getByResellerId($resellerid)
    {
        $model = $this->getModel();
        return $model->where("reseller_id", "=", $resellerid)->get();
    }

    public function getServicesWithResellerByType($type)
    {
        $tblClient = (new Client())->getTable();

        $tblReseller = (new Reseller())->getTable();
        $tblServices = (new ResellerService())->getTable();
        return ResellerService::select($tblServices.'.relid', $tblClient.'.id as reseller_id', $tblClient.'.firstname',$tblClient.'.lastname')
            ->join($tblReseller, $tblReseller.'.id', '=', $tblServices.'.reseller_id')
            ->join($tblClient, $tblClient.'.id', '=', $tblReseller.'.client_id')
            ->where($tblServices.'.type', $type)
            ->get();
    }

    public function getServicesByClientId($clientid, $type, $resellerid = null)
    {
        $query = DB::table("ResellersCenter_ResellersServices");
        $query->where("ResellersCenter_ResellersServices.type", $type)->where("userid", $clientid);
        
        if ($type == self::TYPE_HOSTING) {
            $query->leftJoin("tblhosting", function($join){
                $join->on("tblhosting.id", "=", "ResellersCenter_ResellersServices.relid");
            });
        } elseif($type == self::TYPE_DOMAIN) {
            $query->leftJoin("tbldomains", function($join){
                $join->on("tbldomains.id", "=", "ResellersCenter_ResellersServices.relid");
            });
        } else {
            $query->leftJoin("tblhostingaddons", function($join){
                $join->on("tblhostingaddons.id", "=", "ResellersCenter_ResellersServices.relid");
            });
        }

        if ($resellerid){
            $query->where("reseller_id", $resellerid);
        }

        $services = $query->select("ResellersCenter_ResellersServices.id")->get();

        //Load models
        $result = [];
        foreach ($services as $service) {
            $model = $this->getModel();
            $result[] = $model->find($service->id);
        }
        
        return collect($result);
    }
    
    public function getServicesForExport($clientid, $type)
    {
        $query = DB::table("ResellersCenter_ResellersServices");
        $query->where("ResellersCenter_ResellersServices.type", $type)->where('userid', $clientid);

        if ($type == self::TYPE_HOSTING) {
            $query->leftJoin("tblhosting", function($join){
                $join->on("tblhosting.id", "=", "ResellersCenter_ResellersServices.relid");
            });

            $query->leftJoin("tblproducts", 'tblproducts.id', '=', "tblhosting.packageid");
        } elseif ($type == self::TYPE_DOMAIN) {
            $query->leftJoin("tbldomains", function($join){
                $join->on("tbldomains.id", "=", "ResellersCenter_ResellersServices.relid");
            });
        } else {
            $query->leftJoin("tblhostingaddons", function($join){
                $join->on("tblhostingaddons.id", "=", "ResellersCenter_ResellersServices.relid");
            });

            $query->leftJoin('tbladdons', 'tbladdons.id', '=', 'tblhostingaddons.addonid');
        }

        return $query->get();
    }

    /**
     * Get id of related product/addon/domain
     * 
     * @param int $serviceid
     * @return type 
     */
    public function getByTypeAndRelId($type, $relid)
    {
        $model = $this->getModel();
        $service = $model->where("type", $type)->where("relid", $relid)->first();
        
        return $service;
    }

    /**
     * Get assigned to reseller hosting for datatable
     * 
     * @since 3.0.0
     * @param type $resellerid
     * @param type $dtRequest
     * @return type
     */
    public function getHostingForTable($resellerid, $dtRequest, $clientid = null, $hasResellerInvoice = null)
    {
        $query = DB::table("ResellersCenter_ResellersServices");
        $query->join("tblhosting", function ($join) {
            $join->on("tblhosting.id", "=", "ResellersCenter_ResellersServices.relid");
        });
        $query->join("tblclients", function ($join) {
            $join->on("tblclients.id", "=", "tblhosting.userid");
        });
        $query->join('tblcurrencies', 'tblcurrencies.id', '=', 'tblclients.currency');

        //Apply Filters and get total amount of records
        $query->where("ResellersCenter_ResellersServices.reseller_id", $resellerid);
        $query->where("ResellersCenter_ResellersServices.type", self::TYPE_HOSTING);
        if (!empty($clientid)) {
            $query->where("tblclients.id", $clientid);
        }
        $totalCount = $query->count();

        //Join the rest of the tables
        $query->join("tblproducts", function ($join) {
            $join->on("tblhosting.packageid", "=", "tblproducts.id");
        });
        $query->leftJoin("tblservers", function ($join) {
            $join->on("tblservers.id", "=", "tblhosting.server");
        });

        if ($hasResellerInvoice) {
            $query->leftJoin("ResellersCenter_InvoiceItems as invoiceitems", function ($join) {
                $join->on("invoiceitems.relid", "=", "tblhosting.id");
                $join->where("invoiceitems.type", "=", InvoiceItems::TYPE_HOSTING);
            });
        } else {
            $query->leftJoin("tblinvoiceitems as invoiceitems", function ($join) {
                $join->on("invoiceitems.relid", "=", "tblhosting.id");
                $join->where("invoiceitems.type", "=", WhmcsInvoiceItems::TYPE_HOSTING);
            });
        }

        $filter = $dtRequest->filter;
        if (!empty($filter)) {
            $query->where(function ($query) use ($filter) {
                $query->orWhere("tblhosting.id", "LIKE", "%$filter%")
                    ->orWhere("tblhosting.billingcycle", "LIKE", "%$filter%")
                    ->orWhere("tblhosting.domain", "LIKE", "%$filter%")
                    ->orWhere("tblhosting.amount", "LIKE", "%$filter%")
                    ->orWhere("tblproducts.name", "LIKE", "%$filter%")
                    ->orWhere("tblclients.id", "LIKE", "%$filter%")
                    ->orWhere("tblclients.firstname", "LIKE", "%$filter%")
                    ->orWhere("tblclients.lastname", "LIKE", "%$filter%");
            });
        }

        if ($dtRequest->filters) {
            foreach ($dtRequest->filters as $raw) {
                if (!$raw["value"]) {
                    continue;
                }

                //Add Where clauses
                $filter = DataTable::getFilter($raw["name"]);
                switch ($raw["name"]) {
                    case "BillingCycle":
                        $query->where("tblhosting.billingcycle", $raw["value"]);
                        break;
                    case "PaymentMethod":
                        $query->where("invoiceitems.paymentmethod", $raw["value"]);
                        break;
                    case "Product":
                        $query->where("tblhosting.packageid", $raw["value"]);
                        break;
                    case "ProductType":
                        $query->where("tblproducts.type", $raw["value"]);
                        break;
                    case "Server":
                        $query->where("tblhosting.server", $raw["value"]);
                        break;
                    case "Status":
                        $query->where("tblhosting.domainstatus", $raw["value"]);
                        break;
                }
            }
        }

        $query->select(
            "ResellersCenter_ResellersServices.id",
                    "tblhosting.billingcycle",
                    "tblhosting.domain",
                    "tblhosting.nextduedate",
                    DB::raw("CONCAT(tblcurrencies.prefix, tblhosting.amount ,tblcurrencies.suffix) AS price"),
                    DB::raw("tblhosting.domainstatus as status"),
                    DB::raw("tblproducts.name as product"),
                    DB::raw("tblproducts.type as product_type"),
                    DB::raw("tblproducts.id as product_id"),
                    DB::raw("tblclients.id as client_id"),
                    DB::raw("tblhosting.id as hosting_id"),
                    DB::raw("tblservers.id as server_id"),
                    DB::raw("CONCAT('#', tblclients.id, ' ', tblclients.firstname, ' ', tblclients.lastname) as client")
            );

        $query->groupBy("ResellersCenter_ResellersServices.id");
        $displayAmount = count($query->get());
        $query->take($dtRequest->limit)->skip($dtRequest->offset);
        $query->orderBy($dtRequest->columns[$dtRequest->orderBy], $dtRequest->orderDir);
        $data = $query->get();

        $result = array(
            "data" => $data,
            "displayAmount" => $displayAmount,
            "totalAmount" => $totalCount
        );
        
        return $result;
    }

    /**
     * Get assigned to reseller addons for datatable
     *
     * @param $resellerid
     * @param $dtRequest
     * @param null $clientid
     * @param null $hasResellerInvoice
     * @return array
     */
    public function getAddonsForTable($resellerid, $dtRequest, $clientid = null, $hasResellerInvoice = null)
    {
        $query = DB::table("ResellersCenter_ResellersServices");
        $query->leftJoin("tblhostingaddons", function($join){
            $join->on("tblhostingaddons.id", "=", "ResellersCenter_ResellersServices.relid");
        });
        $query->leftJoin("tblclients", function($join){
            $join->on("tblclients.id", "=", "tblhostingaddons.userid");
        });
        $query->leftJoin("tblhosting", function($join){
            $join->on("tblhosting.id", "=", "tblhostingaddons.hostingid");
        });
        $query->leftJoin("tblproducts", function($join){
            $join->on("tblhosting.packageid", "=", "tblproducts.id");
        });
        $query->leftJoin("tbladdons", function($join){
            $join->on("tbladdons.id", "=", "tblhostingaddons.addonid");
        });
        $query->join('tblcurrencies', 'tblcurrencies.id', '=', 'tblclients.currency');

        if($hasResellerInvoice)
        {
            $query->leftJoin("ResellersCenter_InvoiceItems as invoiceitems", function($join)
            {
                $join->on("invoiceitems.relid",  "=", "tblhostingaddons.id");
                $join->where("invoiceitems.type",   "=", InvoiceItems::TYPE_ADDON);
            });
        }
        else
        {
            $query->leftJoin("tblinvoiceitems as invoiceitems", function($join)
            {
                $join->on("invoiceitems.relid",   "=", "tblhostingaddons.id");
                $join->where("invoiceitems.type",    "=", WhmcsInvoiceItems::TYPE_ADDON);
            });
        }

        //Apply Filters
        $query->where("ResellersCenter_ResellersServices.reseller_id", $resellerid);
        $query->where("ResellersCenter_ResellersServices.type", self::TYPE_ADDON);
        $totalCount = $query->count();
        
        if(!empty($clientid)){
            $query->where("tblclients.id", $clientid);
        }

        $filter = $dtRequest->filter;
        if(!empty($filter))
        {
            $query->where(function($query) use ($filter)
            {
                $query->orWhere("tblhostingaddons.id",              "LIKE", "%$filter%")
                      ->orWhere("tblhostingaddons.billingcycle",    "LIKE", "%$filter%")
                      ->orWhere("tbladdons.name",                   "LIKE", "%$filter%")
                      ->orWhere("tblhosting.domain",                "LIKE", "%$filter%")
                      ->orWhere("tblhostingaddons.recurring",       "LIKE", "%$filter%")
                      ->orWhere("tblclients.id",                    "LIKE", "%$filter%")
                      ->orWhere("tblclients.firstname",             "LIKE", "%$filter%")
                      ->orWhere("tblclients.lastname",              "LIKE", "%$filter%");
            });
        }

        if($dtRequest->filters)
        {
            foreach($dtRequest->filters as $raw)
            {
                if(!$raw["value"])
                {
                    continue;
                }

                //Add Where clauses
                $filter = DataTable::getFilter($raw["name"]);
                switch($raw["name"])
                {
                    case "Addon":
                        $query->where("tbladdons.id", $raw["value"]);
                        break;
                    case "BillingCycle":
                        $query->where(function($where) use ($raw)
                        {
                            $where->orWhere("tblhostingaddons.billingcycle", $raw["value"]);
                            $where->orWhere("tblhostingaddons.billingcycle", Pricing::BILLING_CYCLES[$raw["value"]]);
                        });
                        break;
                    case "PaymentMethod":
                        $query->where("invoiceitems.paymentmethod", $raw["value"]);
                        break;
                    case "Product":
                        $query->where("tblhosting.packageid", $raw["value"]);
                        break;
                    case "ProductType":
                        $query->where("tbladdons.type", $raw["value"]);
                        break;
                    case "Server":
                        $query->where("tblhostingaddons.server", $raw["value"]);
                        break;
                    case "Status":
                        $query->where("tblhostingaddons.status", $raw["value"]);
                        break;
                }
            }
        }
        
        $query->select(
            "ResellersCenter_ResellersServices.id",
            "tblhosting.domain",
            "tblhostingaddons.billingcycle",
            "tblhostingaddons.status",
            "tblhostingaddons.nextduedate",
            DB::raw("tblhostingaddons.id as hostingaddonid"),
            DB::raw("CONCAT(tblcurrencies.prefix, tblhostingaddons.recurring ,tblcurrencies.suffix) AS price"),
            DB::raw("tbladdons.name as addon"),
            DB::raw("tbladdons.id as addon_id"),
            DB::raw("tblhosting.id as hosting_id"),
            DB::raw("tblclients.id as client_id"),
            DB::raw("tblclients.id as client_id"),
            DB::raw("tblproducts.name as product"),
            DB::raw("CONCAT('#', tblclients.id, ' ', tblclients.firstname, ' ', tblclients.lastname) as client")
        );

        $displayAmount = $query->count();
        $query->take($dtRequest->limit)->skip($dtRequest->offset);
        $query->orderBy($dtRequest->columns[$dtRequest->orderBy], $dtRequest->orderDir);
        $data = $query->get();

        $result = array(
            "data" => $data,
            "displayAmount" => $displayAmount,
            "totalAmount" => $totalCount
        );
        
        return $result;
    }
    
    /**
     * Get assigned to reseller domains for datatable
     * 
     * @since 3.0.0
     * @param type $resellerid
     * @param type $dtRequest
     * @return type
     */
    public function getDomainsForTable($resellerid, $dtRequest, $clientid = null)
    {
        $query = DB::table("ResellersCenter_ResellersServices");
        $query->join("tbldomains", function($join){
            $join->on("tbldomains.id", "=", "ResellersCenter_ResellersServices.relid");
        });
        $query->join("tblclients", function($join){
            $join->on("tblclients.id", "=", "tbldomains.userid");
        });
        $query->join('tblcurrencies', 'tblcurrencies.id', '=', 'tblclients.currency');
        
        //Apply Filters
        $query->where("ResellersCenter_ResellersServices.reseller_id", $resellerid);
        $query->where("ResellersCenter_ResellersServices.type", self::TYPE_DOMAIN);
        $totalCount = $query->count();

        if(!empty($clientid)){
            $query->where("tblclients.id", $clientid);
        }
        
        $filter = $dtRequest->filter;
        if(!empty($filter))
        {
            $query->where(function($query) use ($filter){
                $query->orWhere("tbldomains.domain",                "LIKE", "%$filter%")
                      ->orWhere("tbldomains.id",                    "LIKE", "%$filter%")
                      ->orWhere("tbldomains.recurringamount.id",    "LIKE", "%$filter%")
                      ->orWhere("tblclients.id",                    "LIKE", "%$filter%")
                      ->orWhere("tblclients.firstname",             "LIKE", "%$filter%")
                      ->orWhere("tblclients.lastname",              "LIKE", "%$filter%")
                      ->orWhere("tbldomains.registrationperiod",    "LIKE", "%$filter%");
            });
        }

        if($dtRequest->filters)
        {
            foreach($dtRequest->filters as $raw)
            {
                if(!$raw["value"])
                {
                    continue;
                }

                //Add Where clauses
                $filter = DataTable::getFilter($raw["name"]);
                switch($raw["name"])
                {
                    case "Registrar":
                        $query->where("tbldomains.registrar", $raw["value"]);
                        break;
                    case "DomainStatus":
                        $query->where("tbldomains.status", $raw["value"]);
                        break;
                }
            }
        }
        
        $query->select(
            "ResellersCenter_ResellersServices.id", 
            "tbldomains.domain", 
            "tbldomains.status",
            "tbldomains.nextduedate",
            "tbldomains.expirydate",
            "tbldomains.registrar",
            DB::raw("CONCAT(tblcurrencies.prefix, tbldomains.recurringamount ,tblcurrencies.suffix) AS price"),
            DB::raw("tbldomains.registrationperiod as period"),
            DB::raw("tblclients.id as client_id"), 
            DB::raw("tbldomains.id as domain_id"),
            DB::raw("CONCAT('#', tblclients.id, ' ', tblclients.firstname, ' ', tblclients.lastname) as client")
        );

        $displayAmount = $query->count();
        $query->take($dtRequest->limit)->skip($dtRequest->offset);
        $query->orderBy($dtRequest->columns[$dtRequest->orderBy], $dtRequest->orderDir);
        $data = $query->get();

        $result = array(
            "data" => $data,
            "displayAmount" => $displayAmount,
            "totalAmount" => $totalCount
        );
        
        return $result;
    }

    public static function getTableByType($type)
    {
        switch($type)
        {
            case self::TYPE_HOSTING:
                return (new Hosting())->getTable();
            case self::TYPE_ADDON:
                return (new HostingAddon())->getTable();
            case self::TYPE_DOMAIN:
                return (new Domain())->getTable();
            default:
                throw new \Exception("Invalid type provided");
        }
    }

    public function getHostingProductsForGlobalSearch($resellerId, $filter)
    {
        $tblHosting = (new Hosting())->getTable();
        $tblResServ = (new ResellerService())->getTable();
        $tblProducts = (new Product())->getTable();

        $query = DB::table($tblResServ);

        $query->select($tblHosting.".id")
            ->addSelect(DB::raw('"'.SearchTypes::SERVICE_TYPE.'" AS type'))
            ->addSelect(DB::raw("CONCAT(".$tblProducts.".name, '&emsp;', ".$tblHosting.".domain) as name"))
            ->addSelect(DB::raw($tblHosting.".domainstatus as status"))
            ->addSelect(DB::raw($tblHosting.".regdate as date"))
            ->addSelect(DB::raw($tblHosting.".userid as client_id"));

        $query->where($tblResServ.'.type', self::TYPE_HOSTING);
        $query->where($tblResServ.'.reseller_id', $resellerId);

        $query->where(function($query) use($filter, $tblProducts, $tblHosting)
        {
            $query->orWhere($tblProducts.".name", "LIKE", "%$filter%")
                ->orWhere($tblHosting.".id", "LIKE", "%$filter%")
                ->orWhere($tblHosting.".domain", "LIKE", "%$filter%")
                ->orWhere($tblHosting.".regdate", "LIKE", "%$filter%")
                ->orWhere($tblHosting.".amount", "LIKE", "%$filter%");
        });

        $query->leftJoin($tblHosting, $tblHosting.".id", "=", $tblResServ.".relid");
        $query->leftJoin($tblProducts, $tblProducts. ".id", "=", $tblHosting.".packageid");

        return $query;
    }

    public function getHostingAddonsForGlobalSearch($resellerId, $filter)
    {
        $tblHosting = (new HostingAddon())->getTable();
        $tblResServ = (new ResellerService())->getTable();
        $tblAddons = (new Addon())->getTable();

        $query = DB::table($tblResServ);

        $query->select($tblHosting.".id")
            ->addSelect(DB::raw('"'.SearchTypes::ADDON_TYPE.'" AS type'))
            ->addSelect(DB::raw($tblAddons. ".name"))
            ->addSelect($tblHosting. ".status")
            ->addSelect($tblHosting. ".regdate as date")
            ->addSelect(DB::raw($tblHosting. ".userid as client_id"));

        $query->where($tblResServ.'.type', self::TYPE_ADDON);
        $query->where($tblResServ.'.reseller_id', $resellerId);

        $query->where(function($query) use($filter, $tblAddons, $tblHosting)
        {
            $query->orWhere($tblAddons.".name", "LIKE", "%$filter%")
                ->orWhere($tblHosting.".id", "LIKE", "%$filter%")
                ->orWhere($tblHosting.".recurring", "LIKE", "%$filter%")
                ->orWhere($tblHosting.".regdate", "LIKE", "%$filter%");
        });

        $query->leftJoin($tblHosting, $tblHosting.".id", "=", $tblResServ.".relid");
        $query->leftJoin($tblAddons, $tblAddons. ".id", "=", $tblHosting.".addonid");

        return $query;
    }

    public function getHostingDomainsForGlobalSearch($resellerId, $filter)
    {
        $tblDomains = (new Domain())->getTable();
        $tblResServ = (new ResellerService())->getTable();

        $query = DB::table($tblResServ);

        $query->select($tblDomains.".id")
            ->addSelect(DB::raw('"'.SearchTypes::DOMAIN_TYPE.'" AS type'))
            ->addSelect(DB::raw($tblDomains. ".domain as name"))
            ->addSelect(DB::raw($tblDomains. ".status"))
            ->addSelect(DB::raw($tblDomains. ".registrationdate as date"))
            ->addSelect(DB::raw($tblDomains.".userid as client_id"));

        $query->where($tblResServ.'.type', self::TYPE_DOMAIN);
        $query->where($tblResServ.'.reseller_id', $resellerId);

        $query->where(function($query) use($filter, $tblDomains)
        {
            $query->orWhere($tblDomains.".domain", "LIKE", "%$filter%")
                ->orWhere($tblDomains.".recurringamount", "LIKE", "%$filter%")
                ->orWhere($tblDomains.".registrationdate", "LIKE", "%$filter%")
                ->orWhere($tblDomains.".id", "LIKE", "%$filter%");
        });

        $query->leftJoin($tblDomains, $tblDomains.".id", "=", $tblResServ.".relid");
        $query->groupBy($tblDomains. ".id");

        return $query;
    }
}
