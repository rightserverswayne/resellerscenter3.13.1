<?php

namespace MGModule\ResellersCenter\core\hooks;

use DateTime;
use MGModule\ResellersCenter\Core\Helpers\DateFormatHelper;
use MGModule\ResellersCenter\mgLibs\Helpers\DateFormatter;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\repository\ResellersSettings;

class ClientAreaPageViewTicket
{
    public $functions;

    public static $params;

    public function __construct()
    {
        if (!DateFormatHelper::changeDateFormatIsAllowed()) {
            return [];
        }

        $this->functions[30] = function($params) {
            self::$params = $this->setDateFormat($params);
        };

        $this->functions[40] = function($params)
        {
            //Fix for WHMCS 7.2.1
            global $smartyvalues;
            $smartyvalues = self::$params;

            return self::$params;
        };
    }

    private function setDateFormat($params)
    {
        $resellersClientsRepo = new ResellersClients();
        $clientId =  $_SESSION["uid"];
        $resellerClient = $resellersClientsRepo->getByRelid($clientId);

        $format = (new ResellersSettings())->getSetting('dateFormat', $resellerClient->reseller_id, true);
        $dateFormatter = new DateFormatter();
        $whmcsCarbon = new \WHMCS\Carbon();
        $formatWhmcs = $whmcsCarbon->getClientDateFormat(true);

        foreach ($params['descreplies'] as &$descReply) {
            $date = DateTime::createFromFormat($formatWhmcs, $descReply['date']);
            $descReply['date'] = $dateFormatter->format($date, $format, true);
        }

        foreach ($params['ascreplies'] as &$ascReply) {
            $date = DateTime::createFromFormat($formatWhmcs, $ascReply['date']);
            $ascReply['date'] = $dateFormatter->format($date, $format, true);
        }

        return $params;
    }

}