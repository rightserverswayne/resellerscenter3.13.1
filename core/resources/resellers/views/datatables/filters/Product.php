<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Views\Datatables\Filters;

use MGModule\ResellersCenter\Core\Datatable\Filters\Product as ProductFilter;
use MGModule\ResellersCenter\Core\Traits\IsResellerProperty;
use MGModule\ResellersCenter\repository\Contents;

/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Product extends ProductFilter
{
    use IsResellerProperty;

    /**
     * Get filter possible values
     *
     * @param string $search
     * @return array
     */
    public function getData($search = "")
    {
        $repo = new Contents();
        $contents = $repo->getByGroupAndType($this->reseller->group_id, Contents::TYPE_PRODUCT, $search);

        $result = [];
        foreach ($contents as $content)
        {
            $exists = array_search($content->product->group->name, array_column($result, "text"));
            if($exists === false)
            {
                $result[] =
                [
                    "text"      => $content->product->group->name,
                    "children"  => []
                ];

                //Get the new group key
                $exists = array_keys($result)[count($result)-1];
            }

            $result[$exists]["children"][] =
            [
                "id"    => $content->product->id,
                "text"  => $content->product->name
            ];
        }

        return $result;
    }
}