<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\DateFormatHelper;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\mgLibs\Helpers\DateFormatter;
use MGModule\ResellersCenter\models\whmcs\Hosting;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\repository\ResellersSettings;

class ClientAreaPageProductDetails
{
    public $functions;

    public static $params;

    public function __construct()
    {
        if (!DateFormatHelper::changeDateFormatIsAllowed()) {
            return [];
        }

        $this->functions[10] = function($params) {
            self::$params = $this->setServiceData(self::$params);
        };

        $this->functions[30] = function($params) {
            self::$params = $this->setDateFormat(self::$params);
        };

        $this->functions[40] = function($params)
        {
            //Fix for WHMCS 7.2.1
            global $smartyvalues;
            $smartyvalues = self::$params;

            return self::$params;
        };
    }

    private function setServiceData(&$params)
    {
        $invoiceId = Request::get('id');
        $models = Hosting::find($invoiceId);

        if (!$models) {
            return $params;
        }

        foreach ($models->getAttributes() as $key=>$value) {
            $params['serviceData'][$key] = $value;
        }
        return $params;
    }

    private function setDateFormat(&$params)
    {
        $resellersClientsRepo = new ResellersClients();
        $resellerClient = $resellersClientsRepo->getByRelid($params['serviceData']['userid']);

        $format = (new ResellersSettings())->getSetting('dateFormat', $resellerClient->reseller_id, true);
        $dateFormatter = new DateFormatter();

        if ($params['serviceData']['regdate'] != DateFormatter::ZERO_DATE) {
            $params['regdate'] = $dateFormatter->format($params['serviceData']['regdate'], $format);
        }

        if ($params['serviceData']['nextduedate'] != DateFormatter::ZERO_DATE) {
            $params['nextduedate'] = $dateFormatter->format($params['serviceData']['nextduedate'], $format);
        }

        return $params;
    }
}