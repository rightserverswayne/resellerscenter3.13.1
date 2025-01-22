<?php

namespace MGModule\ResellersCenter\controllers\addon\clientarea;

use MGModule\ResellersCenter\collections\SearchCollection;
use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\libs\DataTableButtons\ButtonsFactory;
use MGModule\ResellersCenter\libs\GlobalSearch\DataTableDecorator as Datatable;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\libs\GlobalSearch\SearchTypes;
use MGModule\ResellersCenter\Addon;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;
use MGModule\ResellersCenter\repository\ResellersSettings;

class Search extends AbstractController
{
    public function indexHTML()
    {
        $reseller = Reseller::getLogged();

        return ['tpl'  => 'base', 'vars' => array(
            "reseller" => $reseller,
            'isLagom'         => Addon::isLagom(),
            "gateways" => Helper::getCustomGateways($reseller->id)
        )];
    }

    public function getSearchForTableJSON()
    {
        $searchValue = Request::get("filter");
        $reseller = Reseller::getLogged();
        $dtRequest = Request::getDatatableRequest();

        $collection = new SearchCollection();

        $result = $collection->getGlobalSearchResult($reseller->id, $searchValue, $dtRequest);

        $buttons = $this->getButtons($reseller);

        $datatable = new Datatable(null, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }

    protected function getButtons($reseller)
    {
       $buttons = [];
       try {
           foreach (SearchTypes::getTypes() as $type) {
               $buttonsType = ButtonsFactory::createByName($type);
               $buttons[$type] = $buttonsType->getButtons($reseller);
           }
       } catch (\Exception $exception) {
           $logger = new core\Logger();
           $logger->addNewLog(repository\Logs::ERROR, $exception->getMessage());
       }

       return $buttons;
    }

    public function isActive()
    {
        $settingsRepo = new ResellersSettings();
        $settings = $settingsRepo->getSettings(ResellersSettings::RESELLERS_DEFAULT_CONFIGURATION_ID);
        return $settings['showGlobalSearch'] == 'on';
    }

}