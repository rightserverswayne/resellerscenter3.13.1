<?php

namespace MGModule\ResellersCenter\Core\Helpers;

use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\repository\ResellersSettings;

class DateFormatHelper
{
    public static function changeDateFormatIsAllowed():bool
    {
        $userId = Session::get('uid');
        $isReseller = ResellerHelper::isReseller($userId);
        $isResellerClient = (new ResellersClients())->getByRelid($userId)->exists;

        if ( $isReseller || (!$isResellerClient && !ResellerHelper::getByCurrentURL()->exists) ) {
            return false;
        }
        $resellersClientsRepo = new ResellersClients();
        $resellerClient = $resellersClientsRepo->getByRelid($userId);

        $customDateFormatSetting = (new ResellersSettings())->getSetting('customDateFormat', $resellerClient->reseller_id);

        if (!$customDateFormatSetting || $customDateFormatSetting != 'on') {
            return false;
        }
        return true;
    }

}