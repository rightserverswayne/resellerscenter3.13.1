<?php
namespace MGModule\ResellersCenter\repository;
use MGModule\ResellersCenter\Repository\Source\AbstractRepository;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Documentations
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Documentations extends AbstractRepository
{
    public function determinateModel() 
    {
        return 'MGModule\ResellersCenter\models\Documentation';
    }
    
    public function getForTable($dtRequest)
    {
        $query = DB::table("ResellersCenter_Documentations");
        $total = $query->count();
        $filter = $dtRequest->filter;
        if (!empty($filter))
        {
            $filter = DB::getPdo()->quote("%{$filter}%");
            $query->where("name","LIKE",$filter);
            $query->where(DB::raw("DATE_FORMAT(ResellersCenter_Documentations.created_at, '%Y-%m-%d')"),"LIKE",$filter);
            $query->where(DB::raw("DATE_FORMAT(ResellersCenter_Documentations.updated_at, '%Y-%m-%d')"),"LIKE",$filter);
        }
        
        $filtered = $query->count();
        
        $query->orderBy($dtRequest->columns[$dtRequest->orderBy], $dtRequest->orderDir);
        $query->take($dtRequest->limit)->skip($dtRequest->offset);
        $data = $query->get();
        
        return array("data" => $data, "displayAmount" => $filtered, "totalAmount" => $total);
    }
}
