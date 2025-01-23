<?php
namespace MGModule\ResellersCenter\repository;
use MGModule\ResellersCenter\repository\source\AbstractRepository;
use \Illuminate\Database\Capsule\Manager as DB;

use MGModule\ResellersCenter\repository\ContentsPricing;
use MGModule\ResellersCenter\repository\ContentsSettings;
use MGModule\ResellersCenter\core\Counting;

/**
 * Description of Groups
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Contents extends AbstractRepository
{    
    const TYPES = array(
        "product",
        "addon",
        "domainregister",
        "domaintransfer",
        "domainrenew",
    );
    
    const TYPE_PRODUCT = 'product';
    
    const TYPE_ADDON = 'addon';
    
    const TYPE_DOMAIN_REGISTER = 'domainregister';
    
    const TYPE_DOMAIN_TRANSFER = 'domaintransfer';
    
    const TYPE_DOMAIN_RENEW = 'domainrenew';
 
    //cache for more speed
    public static $contents;

    /**
     * Get content domain type based on provided domain
     *
     * @param $type
     * @return string
     */
    public static function getDomainType($type)
    {
        return "domain{$type}";
    }
    
    public function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\Content';
    }
    
    /**
     * Assign new product/addon/domain for group
     * 
     * @since 3.0.0
     * @param type $groupid
     * @param type $relid
     * @param type $type
     */
    public function createNew($groupid, $relid, $type)
    {
        $content = $this->getModel();
        $content->setData($groupid, $relid, $type);
        $content->save();
        
        return $content->id;
    }
    
    /**
     * Delete content and pricing
     * 
     * @params int contentid
     */
    public function deleteContent($cid)
    {
        //Remove content
        $this->delete($cid);
        
        //Remove pricing for content
        $repo = new ContentsPricing();
        $repo->deletePricing($cid);
    }
    
    /**
     * Remove related contents with pricing
     * 
     * @param type $relid
     * @param type $type
     */
    public function deleteReleatedContents($relid, $type)
    {
        $model = $this->getModel();
        $contents = $model->where("relid", $relid)->where("type",  $type)->get();
        
        foreach($contents as $content)
        {
            $this->deleteContent($content->id);
        }
    }
    
    /**
     * Get assigned product/addon/domain
     * 
     * @since 3.0.0
     * @param type $groupid
     * @param type $relid
     * @return type
     */
    public function getContentByKeys($groupid, $relid, $type)
    {
        //Cache result 
        if(!self::$contents) 
        {
            $all = $this->all();
            foreach($all as $row)
            {
               self::$contents[$row->group_id][$row->relid][$row->type] = $row;
            }
        }

        return self::$contents[$groupid][$relid][$type];
    }
    
    /**
     * Get assigned products/addons/domains
     * 
     * @since 3.0.0
     * @param type $groupid
     * @param type $type (product, addon, domain)
     * @return type
     */
    public function getContentsByGroupAndType($groupid, $type)
    {
        $query = DB::table("ResellersCenter_GroupsContents");
        $query->where("type", "=", $type);
        $query->where("group_id", "=", $groupid);
        
        $result = $query->get();
        
        return $result;
    }

    /**
     * Get assigned to group.
     *
     * @param int  $groupid
     * @param string  $type
     * @param array $filter //global search
     * @param int  $limit
     *
     * @return array
     * @since 3.0.0
     */
    public function getByGroupAndType( $groupid, $type, $filter = [], $limit = null)
    {
        $query = DB::table('ResellersCenter_GroupsContents');
        if($type === self::TYPE_PRODUCT)
        {
            $query->leftJoin('tblproducts', static function( $join){
                $join->on('tblproducts.id', '=', 'ResellersCenter_GroupsContents.relid');
            });
        }
        elseif($type === self::TYPE_ADDON)
        {
            $query->leftJoin('tbladdons', static function( $join){
                $join->on('tbladdons.id', '=', 'ResellersCenter_GroupsContents.relid');
            });
        }
        else
        {
            $query->leftJoin('tbldomainpricing', static function( $join){
                $join->on('tbldomainpricing.id', '=', 'ResellersCenter_GroupsContents.relid');
            });
        }
        
        $query->where('ResellersCenter_GroupsContents.type', $type);
        $query->where('ResellersCenter_GroupsContents.group_id', $groupid);

        if(!empty($filter))
        {
            $query->where(static function( $query) use ($filter, $type){
                if($type === Contents::TYPE_PRODUCT) {
                    $query->orWhere('tblproducts.name', 'LIKE', "%$filter%");
                }
                elseif($type === Contents::TYPE_ADDON) {
                    $query->orWhere('tbladdons.name', 'LIKE', "%$filter%");
                }
                else {
                    $query->orWhere('tbldomainpricing.extension', 'LIKE', "%$filter%");
                }
                
                
                $query->orWhere('ResellersCenter_GroupsContents.id', 'LIKE', "%$filter%");
            });
        }
        
        if(!empty($limit)){
            $query->take($limit);
        }
        
        $query->select(DB::raw("ResellersCenter_GroupsContents.id as contentid"));
        $items = $query->get();
        
        //Get content objects
        $result = [];
        foreach($items as $item) 
        {
            $result[] = $this->find($item->contentid);
        }

        return $result;
    }
    
    /**
     * Get all DISTINCT domains 
     * 
     * @since 3.0.0
     * @param type $groupid
     * @param type $filter
     * @param type $limit
     */
    public function getDomainsByGroup($groupid, $filter = array(), $limit = 0)
    {
        $query = DB::table("ResellersCenter_GroupsContents");
        $query->leftJoin("tbldomainpricing", function($join){
            $join->on("tbldomainpricing.id", "=", "ResellersCenter_GroupsContents.relid");
        });

        //Get all domains types
        $query->where(function($query){
            $query->orWhere("type", Contents::TYPE_DOMAIN_REGISTER)
                  ->orWhere("type", Contents::TYPE_DOMAIN_TRANSFER)
                  ->orWhere("type", Contents::TYPE_DOMAIN_RENEW);
        });
        
        //Filters
        $query->where("ResellersCenter_GroupsContents.group_id", $groupid);
        if(! empty($filter)) {
            $query->where(function($query) use($filter){
                $query->orWhere("tbldomainpricing.extension", "LIKE", "%$filter%");
                $query->orWhere("ResellersCenter_GroupsContents.id", "LIKE", "%$filter%");
            });
        }
        
        $query->select("*" , DB::raw("ResellersCenter_GroupsContents.id as contentid"));
        if($limit != 0) {
            $query->take($limit);
        }
        
        $items = $query->get();
        
        //Get content objects
        $result = array();
        foreach($items as $item) 
        {
            $result[] = $this->find($item->contentid);
        }

        return $result;
    }
        
    /**
     * Get assinged Products/Addons/Domains for datatable
     * 
     * @since 3.0.0
     * @param type $type (product, addon, domain)
     * @param type $groupid
     * @param type $dtRequest
     * @return type
     */
    public function getContentDataForTable($type, $groupid, $dtRequest)
    {
        $query = DB::table('tblcontents')
            ->select('tblcontents.id', 'tblcontents.relid', 'tblcontents.type', 'tblcontents.payment_type', 'tblcontents.counting_type', 'tblcontents.profit_percent', 'tblcontents.profit_rate', 'tblproductgroups.name as product_group')
            ->join('tblproducts', 'tblcontents.relid', '=', 'tblproducts.id')
            ->join('tblproductgroups', 'tblproducts.gid', '=', 'tblproductgroups.id')
            ->where('tblcontents.group_id', $groupid)
            ->where('tblcontents.type', $type);

        if($type == Contents::TYPE_PRODUCT) 
        {
            $query->leftjoin('tblproducts', function($join) {
                $join->on("tblproducts.id","=", "ResellersCenter_GroupsContents.relid");
            });
                
            $query->select(DB::raw("ResellersCenter_GroupsContents.*"),
                "tblproducts.name as {$type}_name",
                "tblproducts.paytype as payment_type"
            );
            
            if(!empty($dtRequest->filter)) {
                $query->where("tblproducts.name","LIKE","%$dtRequest->filter%");
            } 

            $query->where('ResellersCenter_GroupsContents.type','=', 'product');
        }
        elseif($type == Contents::TYPE_ADDON) 
        {
            $query->leftjoin('tbladdons', function($join) {
                $join->on("tbladdons.id","=", "ResellersCenter_GroupsContents.relid");
            });
        
            $query->select(DB::raw("ResellersCenter_GroupsContents.*"),
                "tbladdons.name as {$type}_name",
                "tbladdons.billingcycle as payment_type"
            );
            
            if(!empty($dtRequest->filter)) {
                $query->where("tbladdons.name","LIKE","%$dtRequest->filter%");
            } 

            $query->where('ResellersCenter_GroupsContents.type','=', 'addon');
        }
        else //Domains
        {
            $query->leftjoin('tbldomainpricing', function($join) {
                $join->on("tbldomainpricing.id","=", "ResellersCenter_GroupsContents.relid");
            });

            $query->select(DB::raw("ResellersCenter_GroupsContents.*"),
                "tbldomainpricing.extension as domain_name"
            );
            
            if(!empty($dtRequest->filter)) {
                $query->where("tbldomainpricing.extension","LIKE","%$dtRequest->filter%");
            }   
            
            $domainTypes = array(Contents::TYPE_DOMAIN_REGISTER, Contents::TYPE_DOMAIN_TRANSFER, Contents::TYPE_DOMAIN_RENEW);
            $query->whereIn('ResellersCenter_GroupsContents.type', $domainTypes);
            
            //3 records per one tld
            $dtRequest->limit *= 3; 
            $dtRequest->offset *= 3; 
        }
        
        $query->where('ResellersCenter_GroupsContents.group_id','=',$groupid);
        
        $displayAmount = $query->count();
        $contents = $query->take($dtRequest->limit)->offset($dtRequest->offset)->get();

        foreach($contents as &$content)
        {
            $settings = new ContentsSettings();
            $details = $settings->getConfigByContentId($content->id);
            
            if(! empty($details["type"]))
            {
                $counting = Counting::factory($details["type"]);

                $content->counting_type  = $counting->name;
                $content->profit_percent = $details["settings"]["profit_percent"];
                $content->profit_rate    = $details["settings"]["profit_rate"];
            }
            else
            {
                $content->counting_type  = '';
                $content->profit_percent = '';
                $content->profit_rate    = '';
            }
        }

        $contents = collect($contents)->toArray();

        //Sort
        usort($contents, function($a, $b) use ($dtRequest)
        {
            $orderCol = $dtRequest->columns[$dtRequest->orderBy];
            if(in_array($orderCol, array('product_name', 'payment_type', 'counting_type')))
            {
                if($dtRequest->orderDir == 'asc') {
                    return strnatcmp($a->{$orderCol}, $b->{$orderCol}) < 0 ? -1 : 1;
                }
                else {
                    return strnatcmp($a->{$orderCol}, $b->{$orderCol}) > 0 ? -1 : 1;
                }
            }
            else
            {
                if($dtRequest->orderDir == 'asc') {
                    return ($a->{$orderCol} < $b->{$orderCol}) ? -1 : 1;
                }
                else {
                    return ($a->{$orderCol} > $b->{$orderCol}) ? -1 : 1;
                }
            }
        }); 
        
        if($type == Contents::TYPE_PRODUCT || $type == Contents::TYPE_ADDON) 
        {
            $totalAmount = DB::table("ResellersCenter_GroupsContents")->where("group_id",'=',$groupid)->where("type", $type)->count();
        } 
        else  //Domains
        {
            $totalAmount = DB::table("ResellersCenter_GroupsContents")->where("group_id",'=',$groupid)->whereIn("type", $domainTypes)->count();
            $totalAmount = ceil($totalAmount / 3);
            $displayAmount = ceil($displayAmount / 3);
        }
        
        $result = array("data" => $contents, "displayAmount" => $displayAmount, "totalAmount" => $totalAmount);
        return $result;
    }
}
