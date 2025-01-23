<?php

namespace MGModule\ResellersCenter\Controllers\Addon\Admin;

use MGModule\ResellersCenter\mgLibs\process\AbstractController;
use MGModule\ResellersCenter\models\Content;
use MGModule\ResellersCenter\models\Reseller;
use MGModule\ResellersCenter\models\ResellerPricing;
use MGModule\ResellersCenter\models\whmcs\DomainPricing;
use MGModule\ResellersCenter\repository\Contents as ContentsRepo;
use MGModule\ResellersCenter\repository\ContentsSettings;
use MGModule\ResellersCenter\repository\ContentsPricing;
use MGModule\ResellersCenter\repository\whmcs\Currencies;
use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\core\Counting;
use MGModule\ResellersCenter\core\datatable\DatatableDecorator as Datatable;
use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\core\Request;

/**
 * Description of Contents
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Contents extends AbstractController
{
    /*     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *  Get / Add / Delete
     */

    public function getContentJSON()
    {
        $contentid = Request::get("contentid");

        $contents = new ContentsRepo();
        $result   = $contents->find($contentid);

        return $result->toArray();
    }

    public function getGroupContentsJSON()
    {
        $groupid = Request::get("groupid");
        $type    = Request::get("type");

        $contents = new ContentsRepo();
        $result   = $contents->getByGroupAndType($groupid, $type);

        //Parse models to array - models are not parse automatically in WHMCS6
        foreach ($result as $key => $res)
        {
            $result[$key] = $res->toArray();
        }

        return $result;
    }

    public function addContentToGroupJSON()
    {
        $groupid = Request::get("groupid");
        $relid   = Request::get("relid");
        $type    = Request::get("type");

        $contents  = new ContentsRepo();
        $contentid = $contents->createNew($groupid, $relid, $type);

        //Save default settings
        $cs = new ContentsSettings();
        $cs->saveConfiguration($contentid, $groupid, array("counting_type" => "Difference"));

        //Add pricing FROM WHMCS as DEFAULT admin price
        $cp = new ContentsPricing();
        $cp->setDefaultPricing($contentid);

        EventManager::call("contentAdded", $type, $relid, $groupid);
        return array("success" => Lang::T('settings', 'content', 'create', 'success'));
    }

    public function addMassiveTldToGroupJSON()
    {
        $types = ['domainregister', 'domaintransfer', 'domainrenew'];
        $groupid = Request::get('groupid');
        $actualTldList = collect((new Content())->where('group_id', $groupid)->whereIn('type', $types)->pluck('relid'))->unique()->toArray();
        $tldIdList = collect((new DomainPricing())->whereNotIn('id', $actualTldList)->pluck('id'))->toArray();

        foreach($tldIdList as $tldId)
        {
            foreach($types as $type)
            {
                $contents = new ContentsRepo();
                $contentId = $contents->createNew($groupid, $tldId, $type);

                /* Save default settings */
                $cs = new ContentsSettings();
                $cs->saveConfiguration($contentId, $groupid, array("counting_type" => "Difference"));

                /* Add pricing FROM WHMCS as DEFAULT admin price */
                $cp = new ContentsPricing();
                $cp->setDefaultPricing($contentId);
            }
        }

        EventManager::call("contentMassiveAdded", $groupid);
        return array("success" => Lang::T('settings', 'content', 'create', 'success'));
    }

    public function deleteContentFromGroupJSON()
    {
        $contentid = Request::get("contentid");

        try
        {
            $cids = explode(",", $contentid);
            foreach ($cids as $cid)
            {
                $repo = new ContentsRepo();
                $content = $repo->find($cid);

                if( !isset($resellersInGroup) )
                {
                    $resellersInGroup = Reseller::where('group_id', '=', $content->group_id)->pluck('id');
                    if( $resellersInGroup->count() )
                    {
                        $resellersInGroup = $resellersInGroup->toArray();
                    }
                }

                $pricing = new ResellerPricing();
                $pricing->byRelid($content->relid)
                        ->byType($content->type)
                        ->whereIn('reseller_id', $resellersInGroup)
                        ->delete();

                $repo->deleteContent($cid);
            }
        }
        catch (\Exception $ex)
        {
            return array("error" => $ex->getMessage());
        }

        EventManager::call("contentDeleted", $contentid);
        return array("success" => Lang::T('settings', 'content', 'delete', 'success'));
    }
    /*     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *  Configuration
     */

    /**
     * Load contetns counting settings.
     * This can be a bit confusing since same funcion is used while modal is opening 
     * and when couning type has been change by admin
     * 
     * @return type
     */
    public function getContentConfigJSON()
    {
        //Get currently selected type - only when admin change counting type in modal
        $type      = Request::get("counting_type");
        $contentid = Request::get("contentid");

        $contents = new ContentsSettings();
        $config   = $contents->getConfigByContentId($contentid);

        //if type is different then in current setting load blank configuration
        $result = array();
        if ($type == $config["type"] && !empty($config["type"]))
        {
            $counting = Counting::factory($config["type"], $config["settings"]);
        }
        elseif (!empty($type))
        {
            $counting = Counting::factory($type);
        }
        else
        {
            /**
             * Load Default configuration if content does not have any.
             * Otherwise load counting and setings for content.
             */
            if (empty($config["type"]))
            {
                $counting = Counting::factory("Difference");
            }
            else
            {
                $counting = Counting::factory($config["type"], $config["settings"]);
            }
        }

        $result["name"] = $counting->getName();
        $result["html"] = $counting->getConfigurationHTML();
        return $result;
    }

    public function saveContentConfigJSON()
    {
        $groupid   = Request::get("groupid");
        $contentid = Request::get("contentid");
        $data      = Request::get("data");

        //Validate
        $countingData = $data;
        unset($countingData["counting_type"]);

        $counting = Counting::factory($data["counting_type"], $countingData);
        $errors   = $counting->getValidationErrors();
        if (!empty($errors))
        {
            return array("errors" => $errors);
        }

        $cs = new ContentsSettings();
        $cs->saveConfiguration($contentid, $groupid, $data);

        EventManager::call("contentConfigSaved", $contentid, $groupid);
        return array("success" => Lang::T('settings', 'content', 'config', 'form', 'success'));
    }
    
    
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *  Pricing
     */

    public function getContentAvailablePricingJSON()
    {
        $contentid = Request::get("contentid");

        $contents = new ContentsPricing();
        $cycles   = $contents->getAvailablePricing($contentid);

        return $cycles;
    }

    public function getContentPricingJSON()
    {
        $contentid = Request::get("contentid");

        $cp     = new ContentsPricing();
        $result = $cp->getPricingByContentId($contentid);

        return $result;
    }

    public function saveContentPricingJSON()
    {
        $pricing   = Request::get("pricing");
        $contentid = Request::get("contentid");

        //Validate - omg...
        $errors = array();
        foreach ($pricing as $currency => $prices)
        {
            foreach ($prices as $type => $values)
            {
                foreach ($values as $billingcycle => $value)
                {
                    if ((!is_numeric($value) || $value < 0) && $value != '')
                    {
                        $errors[] = array("currency" => $currency, "type" => $type, "billingcycle" => $billingcycle);
                    }
                }
            }
        }

        if (!empty($errors))
        {
            return array("errors" => $errors);
        }

        $cp = new ContentsPricing();
        $cp->savePricing($contentid, $pricing);

        EventManager::call("contentPricingSaved", $contentid);
        return array("success" => Lang::T('settings', 'content', 'pricing', 'form', 'success'));
    }
    
    
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *  Currency
     */
    public function getCurrenciesRatesJSON()
    {
        $currencyid = Request::get("currencyid");
        $currencies = new Currencies();
        
        $result = empty($currencyid) ? $currencies->getAvailableCurrencies() : $currencies->find($currencyid);
        return $result;
    }
    
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *  Datatable
     */
    public function getContentTableDataJSON()
    {
        $type      = Request::get("type");
        $groupid   = Request::get("groupid");
        $dtRequest = Request::getDatatableRequest();

        $contents = new ContentsRepo();
        $result   = $contents->getContentDataForTable($type, $groupid, $dtRequest);

        $format = array(
            "{$type}_name"   => array("link" => array("relid", $type)),
            "product_group"  => array("default" => Lang::T("empty", "value")),
            "type"           => array("lang" => array("{$type}s", 'table')),
            "payment_type"   => array("lang" => array("{$type}s", 'table')),
            "counting_type"  => array("lang"    => array('counting', 'option'),
                "default" => "empty",
                "class"   => array(array("", "text-danger"))),
            "profit_percent" => array("default" => Lang::T("empty", "value")),
            "profit_rate"    => array("default" => Lang::T("empty", "value"))
        );

        $buttons = array(
            array(
                "type"    => "only-icon",
                "class"   => "open" . ucfirst($type) . "Pricing btn-gold",
                "data"    => array("contentid" => "id"),
                "icon"    => "fa fa-dollar",
                "tooltip" => Lang::T("table", "pricingInfo"),
                "if"      => [["payment_type", "!=", "free"]]
            ),
            array(
                "type"    => "only-icon",
                "class"   => "open" . ucfirst($type) . "Config  btn-primary",
                "data"    => array("contentid" => "id"),
                "icon"    => "fa fa-wrench",
                "tooltip" => Lang::T("table", "configInfo"),
                "if"      => [["payment_type", "!=", "free"]]
            ),
            array(
                "type"    => "only-icon",
                "class"   => "open" . ucfirst($type) . "Delete btn-danger",
                "data"    => array("contentid" => "id"),
                "icon"    => "fa fa-trash-o",
                "tooltip" => Lang::T("table", "deleteInfo")
            ),
        );

        if ($type == 'domain')
        {
            $buttons[] = array(
                "type"    => "only-icon",
                "class"   => "openDomainDetails pull-right btn-success",
                "data"    => array("tldextension" => "domain_name"),
                "icon"    => "fa fa-angle-double-down",
                "tooltip" => Lang::T("table", "domainDetailsInfo")
            );

            $rowAccordion = array(
                "groupBy"     => "domain_name",
                "mainCols"    => array("domain_name", "actions"),
                "contentCols" => array("type", "payment_type", "counting_type", "profit_percent", "profit_rate"),
            );
        }

        $datatable = new Datatable($format, $buttons, $rowAccordion);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }
}
