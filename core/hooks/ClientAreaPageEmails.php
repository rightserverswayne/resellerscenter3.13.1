<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\DateFormatHelper;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\mgLibs\Helpers\DateFormatter;
use MGModule\ResellersCenter\models\whmcs\Quote;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\repository\ResellersSettings;

class ClientAreaPageEmails
{
    public $functions;

    public static $params;

    public function __construct()
    {
        if (!DateFormatHelper::changeDateFormatIsAllowed()) {
            return [];
        }

        $this->functions[10] = function($params) {
            self::$params = $this->setEmailData(self::$params);
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

    private function setEmailData(&$params)
    {
        $data = \WHMCS\Mail\Log::ofClient(Session::get('uid'));
        $numitems = $data->count();
        list($orderby, $sort, $limit) = clientAreaTableInit("emails", "date", "DESC", $numitems);
        $smartyvalues["orderby"] = $orderby;
        $smartyvalues["sort"] = strtolower($sort);
        if ($orderby == "subject") {
            $orderby = "subject";
        } else {
            $orderby = "date";
        }
        $emails = [];

        $data->orderBy($orderby, $sort);
        if ($limit) {
            $limit = explode(",", $limit);
            $data->skip($limit[0])->take($limit[1]);
        }
        foreach ($data->get() as $email) {
            $emailAttachments = $email->attachments ?: [];
            $date = $email->getRawAttribute("date");
            $emails[] = ["id" => $email->id, "date" => \WHMCS\Input\Sanitize::makeSafeForOutput(fromMySQLDate($date, true, true)), "normalisedDate" => $date, "subject" => \WHMCS\Input\Sanitize::makeSafeForOutput($email->subject), "attachmentCount" => count($emailAttachments)];
        }

        $params['emails'] = $emails;

        return $params;
    }

    private function setDateFormat(&$params)
    {
        $resellersClientsRepo = new ResellersClients();
        $resellerClient = $resellersClientsRepo->getByRelid(Session::get('uid'));

        $format = (new ResellersSettings())->getSetting('dateFormat', $resellerClient->reseller_id, true);

        $dateFormatter = new DateFormatter();


        foreach ($params['emails'] as &$email) {
            $email['date'] = $dateFormatter->format($email['normalisedDate'], $format, true);
        }

        return $params;
    }

}