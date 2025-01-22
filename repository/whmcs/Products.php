<?php
namespace MGModule\ResellersCenter\repository\whmcs;
use \MGModule\ResellersCenter\repository\source\AbstractRepository;
use \MGModule\ResellersCenter\models\whmcs\Product;
use \Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Products
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Products extends AbstractRepository
{
    const PAYTYPE_FREE      = "free";

    const PAYTYPE_ONETIME   = "onetime";

    const PAYTYPE_RECURRING = "recurring";

    const FREE_DOMAIN       = "on";

    const FREE_DOMAIN_ONCE  = "once";

    public function determinateModel()
    {
        return Product::class;
    }
       
    public function getAvailable($search = "")
    {
        $model = new Product();
        if($search)
        {
            $result = $model->where("paytype", "!=", self::PAYTYPE_FREE)
                            ->where("name", "LIKE", "%{$search}%")
                            ->get();
        }
        else
        {
            $result = $model->where("paytype", "!=", self::PAYTYPE_FREE)
                            ->get();
        }

        return $result;
    }
    
    public function getProductPaymentType($pid)
    {
        $product = new Product();
        $result = $product->find($pid);
        
        return $result->paytype;
    }
    
    public function getProductDisabledPaymentGateways($pid)
    {
        $product = new Product();
        $result = $product->find($pid);

        if(empty($result->group->disabledgateways))
        {
            return [];
        }

        return explode(',', $result->group->disabledgateways);
    }
    
    public function getByGroupId($groupid)
    {
        $query = DB::table("tblproducts");
        $products = $query->where("gid", $groupid)->get();
        
        return $products;
    }
}
