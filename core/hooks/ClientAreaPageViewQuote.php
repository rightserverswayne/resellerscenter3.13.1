<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\DateFormatHelper;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\mgLibs\Helpers\DateFormatter;
use MGModule\ResellersCenter\models\whmcs\Quote;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\repository\ResellersSettings;

class ClientAreaPageViewQuote
{
    public $functions;

    public static $params;

    public function __construct()
    {
        if (!DateFormatHelper::changeDateFormatIsAllowed()) {
            return [];
        }

        $this->functions[10] = function($params) {
            self::$params = $this->setQuoteData(self::$params);
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

    private function setQuoteData(&$params)
    {
        $invoiceId = Request::get('id');
        $models = Quote::find($invoiceId);

        if (!$models) {
            return $params;
        }

        foreach ($models->getAttributes() as $key=>$value) {
            $params['quoteData'][$key] = $value;
        }
        return $params;
    }

    private function setDateFormat(&$params)
    {
        $resellersClientsRepo = new ResellersClients();
        $resellerClient = $resellersClientsRepo->getByRelid(Session::get('uid'));

        $format = (new ResellersSettings())->getSetting('dateFormat', $resellerClient->reseller_id, true);

        $dateFormatter = new DateFormatter();

        $params['datecreated'] = $dateFormatter->format($params['quoteData']['datecreated'], $format);
        $params['validuntil'] = $dateFormatter->format($params['quoteData']['validuntil'], $format);

        return $params;
    }
}