<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\DateFormatHelper;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\mgLibs\Helpers\DateFormatter;
use MGModule\ResellersCenter\models\whmcs\Hosting;
use MGModule\ResellersCenter\models\whmcs\Quote;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\repository\ResellersSettings;

class ClientAreaPageQuotes
{
    public $functions;

    public static $params;

    public function __construct()
    {
        if (!DateFormatHelper::changeDateFormatIsAllowed()) {
            return [];
        }

        $this->functions[10] = function($params) {
            self::$params = $this->setQuotesData(self::$params);
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

    private function setQuotesData(&$params)
    {
        $userId = Session::get('uid');
        $quotes = Quote::where('userid', $userId)->get();

        foreach ($quotes as $index=>$quote) {
            $attributes = $quote->getAttributes();
            foreach ($attributes as $key=>$value) {
                $params['quotes'][$index][$key] = $value;
            }
            $params['quotes'][$index]['stage'] = getQuoteStageLang($params['quotes'][$index]["stage"]);
            $params['quotes'][$index]["stageClass"] = \WHMCS\View\Helper::generateCssFriendlyClassName($params['quotes'][$index]['stage']);
        }

        return $params;
    }

    private function setDateFormat(&$params)
    {
        $resellersClientsRepo = new ResellersClients();
        $resellerClient = $resellersClientsRepo->getByRelid(Session::get('uid'));

        $format = (new ResellersSettings())->getSetting('dateFormat', $resellerClient->reseller_id, true);
        $dateFormatter = new DateFormatter();

        foreach ($params['quotes'] as &$quote) {
            if ($quote['validuntil'] != DateFormatter::ZERO_DATE) {
                $quote['validuntil'] = $dateFormatter->format($quote['validuntil'], $format);
            }

            if ($quote['datecreated'] != DateFormatter::ZERO_DATE) {
                $quote['datecreated'] = $dateFormatter->format($quote['datecreated'], $format);
            }
        }

        return $params;
    }

}