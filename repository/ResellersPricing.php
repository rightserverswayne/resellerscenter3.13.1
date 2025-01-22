<?php
namespace MGModule\ResellersCenter\repository;

use MGModule\ResellersCenter\models\ResellerPricing;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;

use MGModule\ResellersCenter\repository\source\RepositoryException;
use MGModule\ResellersCenter\repository\source\AbstractRepository;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of ContentsSettings
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ResellersPricing extends AbstractRepository
{
    const TYPE_ADDON = 'addon';
    
    const TYPE_PRODUCT = 'product';
    
    const TYPE_DOMAINREGISTER = 'domainregister';
    
    const TYPE_DOMAINTRANSFER = 'domaintransfer';
    
    const TYPE_DOMAINRENEW = 'domainrenew';
    
    public static $cache;
    
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\ResellerPricing';
    }

    /**
     * Save reseller pricing
     *
     * @param       $resellerid
     * @param       $relid
     * @param       $type
     * @param array $pricing array(<currency_id> => array(<price_type> => array(<billing_cycle> => value)))
     *
     * @throws RepositoryException
     * @since 3.0.0
     */
    public function savePricing($resellerid, $relid, $type, $pricing)
    {
        if(empty($relid)) {
            throw new RepositoryException("invalid_relid");
        }
        
        if(empty($type)) {
            throw new RepositoryException("invalid_type");
        }
        
        try 
        {
            DB::beginTransaction();

            $query = DB::table("ResellersCenter_ResellersPricing");
            $query->where("reseller_id", $resellerid)->where("relid", $relid)->where("type",$type)->delete();
        
            foreach($pricing as $currency => $data)
            {
                foreach($data as $billingcycle => $value)
                {
                    $rp = new ResellerPricing();
                    $rp->setData($resellerid, $relid, $type, $currency, $billingcycle, $value);
                    $rp->save();
                }
            }

            DB::commit();
        }
        catch (\Exception $ex)
        {
            DB::rollBack();
        }
    }

    /**
     * Delete reseller pricing
     *
     * @param int $resellerid
     * @param int $contentid
     * @param null $type
     *
     * @since 3.0.0
     */
    public function deletePricing($resellerid, $contentid = null, $type = null)
    {
        $query = DB::table("ResellersCenter_ResellersPricing");
        $query->where("reseller_id", $resellerid);
                
        if(!empty($contentid) && !empty($type)) 
        {
            $query->where("relid", $contentid);
            if($type == 'domain') 
            {
                $query->where(function($q) {
                    $q->orWhere("type", "domaintransfer");
                    $q->orWhere("type", "domainregister");
                    $q->orWhere("type", "domainrenew");
                });
            }
            else
            {
                $query->where("type", $type);    
            }
        }
        
        $query->delete();
    }

    /**
     * Get reseller pricing for product/addon/domain
     *
     * @param $resellerid
     * @param $relid
     * @param $type
     *
     * @return array
     * @since 3.0.0
     */
    public function getPricingByRelid($resellerid, $relid, $type)
    {
        if(!isset(self::$cache[$resellerid][$relid][$type]))
        {
            $data   = $this->getModel()
                           ->where('reseller_id', $resellerid)
                           ->where('relid', $relid)
                           ->where('type',$type)
                           ->get();
            foreach($data as $values) 
            {
                self::$cache[$values->reseller_id][$values->relid][$values->type][$values->currency][$values->billingcycle] = $values->value;
            }
        }

        $result = [];
        if(self::$cache[$resellerid][$relid][$type])
        {
            foreach (self::$cache[$resellerid][$relid][$type] as $currency => $data) {
                $result[$currency] = array(
                    'relid' => $relid,
                    'currency' => $currency,
                    'pricing' => $data
                );
            }
        }
                
        return $result;
    }
    
    /**
     * Get all configured produtcs/addons/domain in reseller store
     * 
     * @param int $resellerid
     * @return type
     */
    public function getConfiguredByType($resellerid, $type, $currency = null)
    {
        $model = $this->getModel();
        if($currency !== null)
        {
            $result = $model->where("reseller_id", $resellerid)
                ->where("type", $type)->where("currency", $currency)
                ->groupBy("relid")->get();
        }
        else
        {
            $result = $model->where("reseller_id", $resellerid)
                ->where("type", $type)->groupBy("relid")->get();
        }
        
        return $result;
    }
    
    /**
     * Get all configured domain despite domain type in reseller store
     * 
     * @param int $resellerid
     * @return type
     */
    public function getConfiguredDomains($resellerid, $currency = null)
    {
        $model = $this->getModel();
        if($currency !== null)
        {
            $result = $model->where("reseller_id", $resellerid)
                ->where(function($where){
                    $where->orWhere("type", self:: TYPE_DOMAINREGISTER)
                          ->orWhere("type", self:: TYPE_DOMAINTRANSFER)
                          ->orWhere("type", self:: TYPE_DOMAINRENEW);
                })
                ->where("currency", $currency)
                ->groupBy("relid")->get();
        }
        else
        {
            $result = $model->where("reseller_id", $resellerid)
                ->where(function($where){
                    $where->orWhere("type", self:: TYPE_DOMAINREGISTER)
                          ->orWhere("type", self:: TYPE_DOMAINTRANSFER)
                          ->orWhere("type", self:: TYPE_DOMAINRENEW);
                })->groupBy("relid")->get();
        }
        
        return $result;
    }
    
    public function getProducts($resellerid, $dtRequest)
    {
        $query = DB::table("ResellersCenter_ResellersPricing");
        $query->leftJoin("tblproducts", function($join){
            $join->on("tblproducts.id", "=", "ResellersCenter_ResellersPricing.relid");
        });
        
        //Required where
        $query->where("ResellersCenter_ResellersPricing.reseller_id", $resellerid);
        $query->where("ResellersCenter_ResellersPricing.type", Contents::TYPE_PRODUCT);
        
        //Total amount
        $query->groupBy("tblproducts.id");
        $total = count($query->get());
        
        //Additional Filters
        if($dtRequest->filter) {
            $query->where("tblproducts.name", "LIKE", "%$dtRequest->filter%");
        }
        
        //amount after filters
        $displayAmount = count($query->get());
        
        $query->select("tblproducts.name")
              ->addSelect("ResellersCenter_ResellersPricing.type")
              ->addSelect("ResellersCenter_ResellersPricing.relid")
              ->addSelect(DB::raw("GROUP_CONCAT(DISTINCT ResellersCenter_ResellersPricing.billingcycle ORDER BY ResellersCenter_ResellersPricing.billingcycle SEPARATOR ',' ) as billingcycles"));
        
        $query->orderBy($dtRequest->columns[$dtRequest->orderBy], $dtRequest->orderDir);
        $query->take($dtRequest->limit)->skip($dtRequest->offset);
        $result = $query->get();
        
        //Add cart url to result
        foreach($result as $key => $row) {
            $result[$key]->cartUrl = $this->getCartUrl($row->relid, $resellerid);
        }
        
        return array("data" => $result, "amount" => $displayAmount, "totalAmount" => $total);
    }
    
    public function getAddons($resellerid, $dtRequest)
    {
        $query = DB::table("ResellersCenter_ResellersPricing");
        $query->leftJoin("tbladdons", function($join){
            $join->on("tbladdons.id", "=", "ResellersCenter_ResellersPricing.relid");
        });
        
        //Required where
        $query->where("ResellersCenter_ResellersPricing.reseller_id", $resellerid);
        $query->where("ResellersCenter_ResellersPricing.type", Contents::TYPE_ADDON);
        
        //Total amount
        $query->groupBy("tbladdons.id");
        $total = count($query->get());
        
        //Additional Filters
        if($dtRequest->filter) {
            $query->where("tbladdons.name", "LIKE", "%$dtRequest->filter%");
        }
        
        //amount after filters
        $displayAmount = count($query->get());
        
        $query->select("tbladdons.name")
              ->addSelect("ResellersCenter_ResellersPricing.type")
              ->addSelect("ResellersCenter_ResellersPricing.relid")
              ->addSelect(DB::raw("GROUP_CONCAT(DISTINCT ResellersCenter_ResellersPricing.billingcycle ORDER BY ResellersCenter_ResellersPricing.billingcycle SEPARATOR ',' ) as billingcycles"));
        
        $query->orderBy($dtRequest->columns[$dtRequest->orderBy], $dtRequest->orderDir);
        $query->take($dtRequest->limit)->skip($dtRequest->offset);
        $result = $query->get();
        
        return array("data" => $result, "amount" => $displayAmount, "totalAmount" => $total);
    }
    
    public function getDomains($resellerid, $dtRequest)
    {
        $query = DB::table("ResellersCenter_ResellersPricing");
        $query->leftJoin("tbldomainpricing", function($join){
            $join->on("tbldomainpricing.id", "=", "ResellersCenter_ResellersPricing.relid");
        });
        
        //Required where
        $query->where("ResellersCenter_ResellersPricing.reseller_id", $resellerid);
        $query->where(function($group){
            $group->orWhere("ResellersCenter_ResellersPricing.type", Contents::TYPE_DOMAIN_REGISTER)
                  ->orwhere("ResellersCenter_ResellersPricing.type", Contents::TYPE_DOMAIN_TRANSFER)
                  ->orwhere("ResellersCenter_ResellersPricing.type", Contents::TYPE_DOMAIN_RENEW);
        });
        
        //Total amount
        $query->groupBy("tbldomainpricing.id");
        $total = count($query->get());
        
        //Additional Filters
        if($dtRequest->filter) {
            $query->where("tbldomainpricing.extension", "LIKE", "%$dtRequest->filter%");
        }
        
        //amount after filters
        $displayAmount = count($query->get());
        
        $query->select("tbldomainpricing.extension")
              ->addSelect("ResellersCenter_ResellersPricing.type")
              ->addSelect("ResellersCenter_ResellersPricing.relid")
              ->addSelect(DB::raw("GROUP_CONCAT(DISTINCT ResellersCenter_ResellersPricing.billingcycle ORDER BY ResellersCenter_ResellersPricing.billingcycle SEPARATOR ',' ) as billingcycles"));
        
        $query->orderBy($dtRequest->columns[$dtRequest->orderBy], $dtRequest->orderDir);
        $query->take($dtRequest->limit)->skip($dtRequest->offset);
        $result = $query->get();
        
        return array("data" => $result, "amount" => $displayAmount, "totalAmount" => $total);
    }
    
    private function getCartUrl($relid, $resellerid)
    {
        global $CONFIG;
        $reseller = new Reseller($resellerid);
        
        if($reseller->settings->admin->cname)
        {
            $query = http_build_query(array("a" => "add", "pid" => $relid));
            
            $systemUrl = parse_url($CONFIG["SystemURL"]);
            $url = "{$systemUrl["scheme"]}://{$reseller->settings->private->domain}{$systemUrl["path"]}/cart.php?$query";
        }
        else
        {
            $query = http_build_query(array("a" => "add", "pid" => $relid, "resid" => $resellerid));
            $url = "{$CONFIG["SystemURL"]}/cart.php?$query";
        }
        
        return $url;
    }
}
