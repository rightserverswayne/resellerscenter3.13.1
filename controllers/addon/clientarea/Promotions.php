<?php

namespace MGModule\ResellersCenter\controllers\addon\clientarea;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;

use MGModule\ResellersCenter\repository\whmcs\ConfigOptions;
use MGModule\ResellersCenter\repository\whmcs\ConfigOptionGroups;
use MGModule\ResellersCenter\repository\whmcs\Pricing as WhmcsPricing;

use MGModule\ResellersCenter\Core\Resources\Promotions\Promotion;
use MGModule\ResellersCenter\core\datatable\DatatableDecorator as Datatable;
use MGModule\ResellersCenter\core\Request;

use MGModule\ResellersCenter\mgLibs\Lang;

/**
 * Description of Promotions
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Promotions extends AbstractController
{
    public function indexHTML()
    {
        return array(
            'tpl'  => 'base',
            'vars' => []
        );
    }

    public function detailsHTML()
    {
        $promoid  = Request::get("id");
        $reseller = Reseller::getLogged();

        //Load promotion if id is provided
        $promotion = $promoid ? $reseller->promotions->find($promoid) : null;

        //Remove doubled billingcycle
        $billingcycles = WhmcsPricing::BILLING_CYCLES;
        unset($billingcycles["Free"]);
        
        //Config Options
        $configOptions = [];
        $repo = new ConfigOptions();
        foreach($repo->all() as $option)
        {
            $configOptions[$option->id] = $option;
        }
        
        return [
            'tpl'  => 'details/base',
            'vars' => [
                "promotion" => $promotion,
                "periods"   => preg_filter('/$/', 'Years', array_keys(WhmcsPricing::DOMAIN_PEROIDS)),
                "cycles"    => array_combine(array_flip($billingcycles), array_flip($billingcycles)),
                "configoptions" => $configOptions
            ]
        ];
    }

    public function saveJSON()
    {
        $data     = Request::get("promotion");
        $reseller = Reseller::getLogged();

        $promotion = new Promotion($data["id"], null, $reseller);
        $promotion->updateOrCreate($data);

        return ["success" => Lang::T('save', 'success')];
    }

    public function deleteJSON()
    {
        $promoid  = Request::get("id");
        $reseller = Reseller::getLogged();

        $promotion = $reseller->promotions->find($promoid);
        $promotion->delete();

        return ["success" => Lang::T('delete', 'success')];
    }

    public function getResellerContentsJSON()
    {
        $search   = Request::get("term");
        $reseller = Reseller::getLogged();

        //Filter
        $result   = [];
        $products = $reseller->contents->products->search($search, ["id", "name"]);
        foreach ($products as $product)
        {
            $arr              = $product->toArray();
            $arr["groupname"] = Lang::T("details", "appliesto", "groupname", "products");
            $result[]         = $arr;
        }

        $addons = $reseller->contents->addons->search($search, ["id", "name"]);
        foreach ($addons as $addon)
        {
            $arr              = $addon->toArray();
            $arr["groupname"] = Lang::T("details", "appliesto", "groupname", "addons");
            $result[]         = $arr;
        }

        $domains = $reseller->contents->domains->search($search, ["id", "extension"]);
        foreach ($domains as $domain)
        {
            $arr              = $domain->toArray();
            $arr["groupname"] = Lang::T("details", "appliesto", "groupname", "domains");
            $result[]         = $arr;
        }

        return $result;
    }

    public function getConfigOptionsJSON()
    {
        $repo   = new ConfigOptionGroups();
        $groups = $repo->all();

        $result = [];
        foreach ($groups as $group)
        {
            $result[] = [
                "name"    => $group->name,
                "options" => $group->options->toArray(),
            ];
        }

        return $result;
    }

    public function getForTableJSON()
    {
        $filter    = Request::get("filter");
        $dtRequest = Request::getDatatableRequest();
        $reseller  = Reseller::getLogged();

        //TODO: Add note to code as tooltip
        $format = [
            "recurring" =>
            [
                "lang"  => ['table', 'recurringvalue'],
                "class" => [
                    ["0", "text-grey"],
                    ["1", "text-success"]
                ]
            ],
        ];

        $buttons = [
            [
                "type"    => "only-icon",
                "href"    => ["id", "promotion_rc"],
                "class"   => "openEditPromo btn-primary",
                "data"    => array("promoid" => "id"),
                "icon"    => "fa fa-edit",
                "tooltip" => Lang::T('table', 'editInfo')
            ],
            [
                "type"    => "only-icon",
                "class"   => "openDeletePromo btn-danger",
                "data"    => array("promoid" => "id"),
                "icon"    => "fa fa-trash-o",
                "tooltip" => Lang::T('table', 'deleteInfo')
            ],
        ];

        $data      = $reseller->promotions->getForTable($dtRequest, $filter);
        $datatable = new Datatable($format, $buttons);
        $datatable->parseData($data["data"], $data["displayAmount"], $data["totalAmount"]);

        return $datatable->getResult();
    }
}
