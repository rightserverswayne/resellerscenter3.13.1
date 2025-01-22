<?php
namespace MGModule\ResellersCenter\Core\Datatable\Filters;

use MGModule\ResellersCenter\Core\Datatable\AbstractFilter;
use MGModule\ResellersCenter\Repository\Whmcs\Products;

/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Product extends AbstractFilter
{
    public function getData($search = "")
    {
        $repo       = new Products();
        $products   = $repo->getAvailable($search);

        $result = [];
        foreach ($products as $product)
        {
            $exists = array_search($product->group->name, array_column($result, "text"));
            if($exists === false)
            {
                $result[] =
                [
                    "text"      => $product->group->name,
                    "children"  => []
                ];
            }

            $result[$exists]["children"][] =
            [
                "id"    => $product->id,
                "text"  => $product->name
            ];
        }

        return $result;
    }
}