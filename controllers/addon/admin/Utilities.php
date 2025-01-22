<?php

namespace MGModule\ResellersCenter\Controllers\Addon\Admin;

use MGModule\ResellersCenter\core\datatable\DatatableDecorator as Datatable;
use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\core\FileUploader;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Helpers\ExportCSV;
use MGModule\ResellersCenter\Helpers\ExportDataHelper;
use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;
use MGModule\ResellersCenter\models\whmcs\Client;
use MGModule\ResellersCenter\repository\Documentations;
use MGModule\ResellersCenter\repository\Logs as LogsRepo;
use MGModule\ResellersCenter\repository\Resellers as ResellersRepo;
use MGModule\ResellersCenter\repository\CreditLineHistories as CreditLinesLogsRepo;
use MGModule\ResellersCenter\repository\ResellersSettings;

/**
 * Description of Statistics
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Utilities extends AbstractController
{
    public function indexHTML()
    {
        return [
            'tpl'  => 'base',
            'vars' => []
        ];
    }

    public function integrationCodeHTML()
    {
        return [
            'tpl'  => 'integrationCode/base',
            'vars' => []
        ];
    }

    public function creditLinesLogsHTML()
    {
        return [
            'tpl'  => 'creditLinesLogs/base',
            'vars' => []
        ];
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
     *  LOGS
     */
    public function logsHTML()
    {
        return [
            'tpl'  => 'logs/base',
            'vars' => []
        ];
    }

    public function getLogsForTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();

        $repo   = new LogsRepo();
        $result = $repo->getDataForTable($dtRequest);

        $format = [
            "admin"    => ["default" => "-"],
            "reseller" => ["link" => ["reseller_id", "reseller"], "default" => "-"],
            "client"   => ["link" => ["client_id", "client"], "default" => "-"],
        ];

        $datatable = new Datatable($format);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }

    public function getCreditLinesLogsForTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();

        $repo   = new CreditLinesLogsRepo();
        $result = $repo->getDataForTable($dtRequest);

        $format = [
            "client"   => ["link" => ["clientId", "client"], "default" => "-"],
            "invoiceId"   => ["link" => ["invoiceId", "invoice", "invoiceType"], "default" => Lang::absoluteT('general','invoices','invoiceDeleted')],
            "invoiceType"   => ["lang" => ["creditLinesLogs", "invoiceType"]]
        ];

        $datatable = new Datatable($format);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
     *  LOGS
     */
    public function resellerDocumentationHTML()
    {
        return [
            'tpl'  => 'resellerDocumentation/base',
            'vars' => []
        ];
    }

    public function documentationDetailsHTML()
    {
        $id = Request::get("id");

        $repo          = new Documentations();
        $documentation = $repo->find($id);

        $resellersRepo = new ResellersRepo();
        $resellers     = $resellersRepo->all();

        return [
            'tpl'  => 'resellerDocumentation/details/base',
            'vars' => [
                "documentation" => $documentation,
                "resellers"     => $resellers
            ]
        ];
    }

    public function saveDocumentationJSON()
    {
        $id        = Request::get("id");
        $name      = Request::get("name");
        $resellers = Request::get("resellers");

        $repo = new Documentations();
        if (empty($id))
        {
            $repo->create(["name" => $name]);

            EventManager::call("docsCreated", $name);
        }
        else
        {
            $content = Request::get("content");
            $repo->update($id, ["name" => $name, "content" => $content]);

            $this->setDocumentationForResellers($id, $resellers);

            EventManager::call("docsUpdated", $id, $name);
        }

        return ["success" => Lang::T('save', 'success')];
    }

    private function setDocumentationForResellers($did, $resellersids)
    {
        $repo = new ResellersSettings();
        $repo->massDelete("documentation", $did);

        foreach ($resellersids as $rid)
        {
            $repo->saveSingleSetting($rid, "documentation", $did);
        }
    }

    public function saveDocumentationPdfJSON()
    {
        $id = Request::get("id");

        $docsDir  = ADDON_DIR . "/storage/documentations/";
        $filename = basename($_FILES["pdf"]["name"]);
        $file     = new FileUploader("pdf", $filename, $docsDir);

        if ($file->isPDF())
        {
            $result = $file->upload();

            if ($result == "success")
            {
                EventManager::call("docsPDFUploaded", $id);

                $repo = new Documentations();
                $repo->update($id, ["pdfpath" => "modules/addons/ResellersCenter/storage/documentations/" . $filename]);

                return [
                    "filename"    => $filename,
                    "htmlpdfpath" => "../modules/addons/ResellersCenter/storage/documentations/" . $filename
                ];
            }
            else
            {
                return ["error" => $result];
            }
        }
        else
        {
            return ["error" => Lang::T('resellerDocumentation', 'invalidfiletype')];
        }
    }

    public function deleteDocumentationPdfJSON()
    {
        $id            = Request::get("id");
        $repo          = new Documentations();
        $documentation = $repo->find($id);

        unlink(ROOTDIR . DS . $documentation->pdfpath);
        $documentation->pdfpath = null;
        $documentation->save();

        return ["success" => Lang::T('save', 'success')];
    }

    public function deleteDocumentationJSON()
    {
        $id = Request::get("id");

        //Check if not documentation is not default
        $settings = new ResellersSettings();
        $default  = $settings->where("setting", "=", "documentation")->where("reseller_id", "=", 0)->first();
        if ($id == $default->value)
        {
            return ["error" => Lang::T('delete', 'error', 'documentationDefault')];
        }

        //Reassign resellers to default one
        $toChange = $settings->where("setting", "=", "documentation")->where("value", "=", $id)->where("private", "=", 0)->get();
        foreach ($toChange as $setting)
        {
            $settings->saveSingleSetting($setting->reseller_id, "documentation", $default->value);
        }

        $repo = new Documentations();
        $repo->delete($id);

        return ["success" => Lang::T('delete', 'success')];
    }

    public function getDocumentationsForTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();
        $repo      = new Documentations();
        $result    = $repo->getForTable($dtRequest);

        $buttons = [
            [
                "type"    => "only-icon",
                "class"   => "openDetails btn-primary",
                "data"    => ["documentationid" => "id"],
                "icon"    => "fa fa-pencil-square-o",
                "tooltip" => Lang::T('table', 'detailsTooltip')
            ],
            [
                "type"    => "only-icon",
                "class"   => "openDelete btn-danger",
                "data"    => ["documentationid" => "id"],
                "icon"    => "fa fa-trash-o",
                "tooltip" => Lang::T('table', 'deleteTooltip')
            ],
        ];

        $datatable = new Datatable([], $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *  DATA EXPORT
     */
    public function dataExportHTML()
    {
        $vars['resellers'] = $this->getResellers();

        return [
            'tpl'  => 'dataExport/base',
            'vars' => $vars
        ];
    }

    public function getDataForTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();
        $result = ExportDataHelper::getDataForTable($dtRequest);

        $format = [
            "data" => ["default" => "-"],
        ];

        $buttons = [
            [
                "type"    => "only-icon",
                "class"   => "openExportModal btn-primary",
                "data"    => ["dataType" => "data"],
                "icon"    => "fa fa-download",
                "tooltip" => Lang::T('table', 'exportTooltip')],
        ];

        $datatable = new Datatable($format, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }

    public function processExportDataHTML()
    {
        $dataType   = Request::get("dataType");
        $resellerId = Request::get('resellerId');

        $export = new ExportCSV($this->getClassFotDataType($dataType), (int)$resellerId);
        $export->download();
    }

    private function getClassFotDataType($dataTypeName)
    {
        $helper = new ExportDataHelper();
        return $helper->getExportDataTypes()[$dataTypeName];
    }

    public function getResellers()
    {
        $resellersRepo = new ResellersRepo();
        $resellers     = $resellersRepo->all();

        $return[] = [
            'resellerId'   => '',
            'resellerData' => [
                'firstname' => 'All',
                'lastname'  => ''
            ],
        ];

        foreach ($resellers as $reseller)
        {
            $resellerDetails = Client::find($reseller->client_id);

            $return[] = ['resellerId' => $reseller->id, 'resellerData' => $resellerDetails->toArray()];
        }

        return $return;
    }

}
