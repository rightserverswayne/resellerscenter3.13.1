<?php

namespace MGModule\ResellersCenter\controllers\addon\clientarea;

use Exception;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\mgLibs\Helpers\UrlGenerator;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;
use MGModule\ResellersCenter\repository\ResellersPricing;
use MGModule\ResellersCenter\repository\ContentsPricing;
use MGModule\ResellersCenter\repository\Contents;
use MGModule\ResellersCenter\repository\whmcs\Pricing as PricingRepo;
use MGModule\ResellersCenter\repository\whmcs\Currencies;
use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\core\datatable\DatatableDecorator as Datatable;
use MGModule\ResellersCenter\core\helpers\ClientAreaHelper as CAHelper;
use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\core\Request;

/**
 * Description of Pricing
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Pricing extends AbstractController
{
    public function indexHTML()
    {
        $vars = [];

        $currencies         = new Currencies();
        $vars["currencies"] = $currencies->getAvailableCurrencies();

        $reseller = Reseller::getLogged();
        $vars["settings"] = $reseller->settings;

        return array(
            "tpl"  => "base",
            "vars" => $vars
        );
    }

    /**
     * Get Product Or Addons that are enabled by Admin
     * in Resellers Center Addon
     *
     * @throws Exception
     * @return array
     * @since 3.0.0
     */
    public function getAvailableItemsJSON()
    {
        session_write_close();
        $type     = Request::get('type');
        $filter   = Request::get('term');
        $reseller = Reseller::getLogged();

        $repo = new Contents();
        if( in_array($type, [Contents::TYPE_ADDON, Contents::TYPE_PRODUCT], true) )
        {
            $contents = $repo->getByGroupAndType($reseller->group_id, $type, $filter);
        }
        else
        {
            $contents = $repo->getDomainsByGroup($reseller->group_id, $filter);
        }

        $result   = [];
        $existing = [];
        $pricing  = new ResellersPricing();

        foreach( $contents as &$content )
        {
            $config = $content->getConfig();
            if( empty($config['type']) )
            {
                continue;
            }

            $exists = $pricing->getPricingByRelid($reseller->id, $content->relid, $type);

            if( !$exists && in_array($type, [Contents::TYPE_ADDON, Contents::TYPE_PRODUCT], true) )
            {
                $result[$content->id]['relid'] = $content->relid;
                if( $type === Contents::TYPE_PRODUCT )
                {
                    $result[$content->id]['name'] = $content->product->name;
                }
                else
                {
                    $result[$content->id]['name'] = $content->addon->name;
                }
            }
            elseif( !$exists && in_array($type, [Contents::TYPE_DOMAIN_REGISTER, Contents::TYPE_DOMAIN_TRANSFER, Contents::TYPE_DOMAIN_RENEW], true) ) //Domain
            {
                $types = [Contents::TYPE_DOMAIN_REGISTER, Contents::TYPE_DOMAIN_TRANSFER, Contents::TYPE_DOMAIN_RENEW];
                foreach( $types as $type )
                {
                    $exists = $pricing->getPricingByRelid($reseller->id, $content->relid, $type);
                    if( $exists ) break;
                }

                if( !$exists && !in_array($content->domain->extension, $existing, false) )
                {
                    $result[$content->domain->extension]['relid'] = $content->relid;
                    $result[$content->domain->extension]['name']  = $content->domain->extension;
                    $existing[]                                   = $content->domain->extension;
                }
            }
        }

        return $result;
    }

    public function getAvailableBillingCyclesJSON()
    {
        $relid    = Request::get("relid");
        $type     = Request::get("type");
        $reseller = Reseller::getLogged();

        $contents = new Contents();
        $content  = $contents->getContentByKeys($reseller->group_id, $relid, $type);

        $cp   = new ContentsPricing();
        $data = $cp->getPricingByContentId($content->id);

        $result = array();
        foreach ($data as $values)
        {
            foreach ($values["pricing"] as $billingcycle => $price)
            {
                if ($price >= 0)
                {
                    $result[$values["currency"]][$billingcycle][$values["type"]] = $price;
                }
            }
        }

        return $result;
    }

    /**
     * Save reseller pricing. 
     * If validation will return errors then function will return errors array
     * with currency and billingcycle information and type of the error
     * 
     * @TODO: rewrite this... 
     * 
     * @params type $relid
     * @params type $pricing
     * @params type $type
     * @return type
     */
    public function savePricingJSON()
    {
        $relid    = Request::get("relid");
        $pricing  = Request::get("pricing");
        $type     = Request::get("type");
        $reseller = Reseller::getLogged();

        $contents = new Contents();
        $content  = $contents->getContentByKeys($reseller->group_id, $relid, $type);

        //Check if pricing is enabled by Admin
        $pricingToSave = array();

        $cp   = new ContentsPricing();
        $data = $cp->getPricingByContentId($content->id);
        foreach ($data as $values)
        {
            foreach ($values["pricing"] as $billingcycle => $price)
            {
                //Check if pricing is not disabled by admin and is not disabled by reseller - then add it to list
                if ($values["type"] == ContentsPricing::TYPE_ADMINPRCIE && $price >= 0)
                {
                    if ($pricing[$values["currency"]][$billingcycle] !== '' && is_numeric($pricing[$values["currency"]][$billingcycle]) !== null)
                    {
                        $pricingToSave[$values["currency"]][$billingcycle] = $pricing[$values["currency"]][$billingcycle];
                    }
                    else
                    {
                        continue;
                    }
                }
            }
        }
        
        //Validate pricing
        $errors = $this->validatePricing($data, $pricingToSave, $content->type);

        //Don't save pricing if there are any errors
        if (!empty($errors))
        {
            return array("errors" => $errors);
        }

        $rp = new ResellersPricing();
        $rp->savePricing($reseller->id, $relid, $type, $pricingToSave);

        EventManager::call("pricingUpdated", $reseller->id, $type, $relid);
        return array("success" => Lang::T('save', 'success'));
    }

    public function getPricingJSON()
    {
        $relid    = Request::get("relid");
        $type     = Request::get("type");
        $reseller = Reseller::getLogged();

        $rp     = new ResellersPricing();
        $result = $rp->getPricingByRelid($reseller->id, $relid, $type);

        return $result;
    }

    public function deletePricingJSON()
    {
        $relid    = Request::get("relid");
        $type     = Request::get("type");
        $reseller = Reseller::getLogged();

        if (!empty($relid) && !empty($type))
        {
            $rp = new ResellersPricing();
            $rp->deletePricing($reseller->id, $relid, $type);
        }
        else
        {
            return array("error" => "Relid or type not provided");
        }

        EventManager::call("pricingDeleted", $reseller->id, $relid);
        return array("success" => Lang::T('delete', 'success'));
    }

    public function getCurrenciesRatesJSON()
    {
        $currencyid = Request::get("currencyid");
        $currencies = new Currencies();

        $result = empty($currencyid) ? $currencies->getAvailableCurrencies() : $currencies->find($currencyid);
        return $result;
    }

    public function getPricingForTableJSON()
    {
        $type      = Request::get("type");
        $dtRequest = Request::getDatatableRequest();
        $reseller  = Reseller::getLogged();

        $repo = new ResellersPricing();
        if ($type == Contents::TYPE_PRODUCT)
        {
            $result    = $repo->getProducts($reseller->id, $dtRequest);
            $buttons[] = array("type" => "only-icon", "class" => "copyCartUrl btn-warning", "data" => array("cartUrl" => "cartUrl"), "icon" => "fa fa-copy", "tooltip" => Lang::T('table', 'cartUrl'));
        }
        elseif ($type == Contents::TYPE_ADDON)
        {
            $result = $repo->getAddons($reseller->id, $dtRequest);
//            $buttons[] = array("type" => "only-icon", "class" => "copyCartUrl btn-warning", "data" => array("relid" => "relid"), "icon" => "fa fa-copy", "tooltip" => Lang::T('table','cartUrl'));
        }
        else
        { //Domains!
            $result = $repo->getDomains($reseller->id, $dtRequest);
        }

        //Translate billing cycles
        foreach ($result["data"] as $data)
        {
            $billingcycles = $this->reOrderBillingCycles(explode(",", $data->billingcycles));
            foreach ($billingcycles as $key => &$billing)
            {
                $suffix = "";
                if (($data->type == ResellersPricing::TYPE_ADDON || $data->type == ResellersPricing::TYPE_PRODUCT) && !in_array($billing, PricingRepo::SETUP_FEES))
                {
                    $billing = Lang::T("billingcycles", $billing);
                }
                elseif ($data->type == ResellersPricing::TYPE_DOMAINREGISTER || $data->type == ResellersPricing::TYPE_DOMAINTRANSFER || $data->type == ResellersPricing::TYPE_DOMAINRENEW)
                {
                    $suffix  = Lang::T("domainperiods", "years");
                    $billing = Lang::T("domainperiods", "numbers", $billing);
                }
                else //Setup fee
                {
                    unset($billingcycles[$key]);
                }
            }

            $data->billingcycles = empty($billingcycles) ? Lang::T("billingcycles", "free") : implode(" | ", $billingcycles) . " " . $suffix;
        }

        $buttons = [
            [
                "type" => "only-icon", 
                "class" => "openPricingModal btn-primary", 
                "data" => ["relid" => "relid"],
                "icon" => "fa fa-wrench", 
                "tooltip" => Lang::T('table', 'configInfo'),
                "if" => [["billingcycles", "!=", Lang::T("billingcycles", "free")]]
            ],
            [
                "type" => "only-icon", 
                "class" => "openDeleteModal btn-danger", 
                "data" => ["relid" => "relid"],
                "icon" => "fa fa-trash-o", 
                "tooltip" => Lang::T('table', 'deleteInfo')
            ]
        ];

        if ($type == Contents::TYPE_PRODUCT) {
            $generateLinkBtnParams = [
                "type" => "only-icon",
                "class" => "openLinkModal btn-primary",
                "data" => ["relid" => "relid"],
                "icon" => "fa fa-link",
                "tooltip" => Lang::T('table', 'linkInfo')
            ];
            $buttons[] = $generateLinkBtnParams;
        }
        
        $datatable = new Datatable(null, $buttons);
        $datatable->parseData($result["data"], $result["amount"], $result["totalAmount"]);

        return $datatable->getResult();
    }

    private function validatePricing($data, $pricingToSave, $contentType)
    {
        $errors = array();
        foreach ($data as $values)
        {
            foreach ($values["pricing"] as $billingcycle => $price)
            {
                //Skip empty
                if ($pricingToSave[$values["currency"]][$billingcycle] == "" && (!(strpos($billingcycle, "setupfee") && $pricingToSave[$values["currency"]][array_search($billingcycle, PricingRepo::SETUP_FEES)]) || stripos($contentType,'domain') !== false ))
                {
                    continue;
                }

                //If provided price is not a number
                if (!is_numeric($pricingToSave[$values["currency"]][$billingcycle]))
                {
                    $errors[] = array("currency" => $values["currency"], "billingcycle" => $billingcycle, "error" => "");
                    continue;
                }

                //Check if setupfees are enabled with recurring
                if (strpos($billingcycle, "setupfee") !== false && !$this->isCheckEnabledRecurring($billingcycle, $pricingToSave, $values["currency"]) && ($contentType == Contents::TYPE_ADDON || $contentType == Contents::TYPE_PRODUCT))
                {
                    $recurring = array_search($billingcycle, PricingRepo::SETUP_FEES);
                    $errors[]  = array("currency" => $values["currency"], "billingcycle" => $recurring, "error" => "");
                    continue;
                }

                //Check if pricing is higher then lowest possible
                if ($values["type"] == ContentsPricing::TYPE_HIGHESTPRICE && $price < $pricingToSave[$values["currency"]][$billingcycle])
                {
                    $errors[] = array("currency" => $values["currency"], "billingcycle" => $billingcycle, "error" => ContentsPricing::TYPE_HIGHESTPRICE);
                    continue;
                }

                //Check if pricing is lower the lowest possible
                if ($values["type"] == ContentsPricing::TYPE_LOWESTPRICE && $price > $pricingToSave[$values["currency"]][$billingcycle])
                {
                    $errors[] = array("currency" => $values["currency"], "billingcycle" => $billingcycle, "error" => ContentsPricing::TYPE_LOWESTPRICE);
                    continue;
                }
            }
        }

        return $errors;
    }

    private function isCheckEnabledRecurring($setupfee, $pricing, $currency)
    {
        $billingcycle = array_search($setupfee, PricingRepo::SETUP_FEES);

        if (!empty($pricing[$currency][$billingcycle]) || $pricing[$currency][$billingcycle] === "0")
        {
            return true;
        }

        return false;
    }

    private function reOrderBillingCycles($billingcycles)
    {
        $pattern = array("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "tsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially", "triennially");
        foreach ($pattern as $key => $cycle)
        {
            if (!in_array($cycle, $billingcycles))
            {
                unset($pattern[$key]);
            }
        }

        return array_values($pattern);
    }

    public function generateCartURLJSON()
    {
        $relid     = Request::get('relid');
        $urlGenerator = new UrlGenerator();

        $url = $urlGenerator->generateFriendlyUrlForProductById($relid);
        $urlGroup = $urlGenerator->generateFriendlyUrlForProductGroupByProductId($relid);

        return ["productLink"=>$url, 'productGroupLink' => $urlGroup];
    }
}
