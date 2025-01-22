<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\DateFormatHelper;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\mgLibs\Helpers\DateFormatter;
use MGModule\ResellersCenter\models\whmcs\Hosting;
use MGModule\ResellersCenter\models\whmcs\Ticket;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\repository\ResellersSettings;
use MGModule\ResellersCenter\repository\whmcs\Tickets;

class ClientAreaPageSupportTickets
{
    public $functions;

    public static $params;

    public function __construct()
    {
        $this->functions[10] = function($params) {
            self::$params = $this->setDateFormat($params);
        };

        $this->functions[20] = function($params)
        {
            //Fix for WHMCS 7.2.1
            global $smartyvalues;
            $smartyvalues = self::$params;

            return self::$params;
        };
    }

    private function setDateFormat(&$params)
    {
        if (!DateFormatHelper::changeDateFormatIsAllowed()) {
            return $params;
        }

        $resellersClientsRepo = new ResellersClients();
        $dateFormatter = new DateFormatter();

        foreach ($params['tickets'] as &$ticket) {
            $resellerClient = $resellersClientsRepo->getByRelid($params['client']->id);
            $format = (new ResellersSettings())->getSetting('dateFormat', $resellerClient->reseller_id, true);
            $ticket['lastreply'] = $dateFormatter->format(preg_replace('/[(]|[)]/', '', $ticket['normalisedLastReply']), $format, true);
        }

        return $params;
    }
}