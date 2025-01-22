<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\repository\ResellersClients;

class AdminAreaClientSummaryPage
{
    const CLIENT_BELONGS_MESSAGE = 'clientBelongsMessage';

    public $functions;

    /**
     * Assign anonymous function
     */
    public function __construct()
    {
        $this->functions[80] = function($vars) {
            return $this->addClientInfo($vars['userid']);
        };
    }

    private function addClientInfo($userid)
    {
        require_once ROOTDIR . DS . 'configuration.php';
        global $CONFIG, $customadminpath;

        $resellersClientRepo = new ResellersClients();
        $reseller = $resellersClientRepo->getResellerByClientId($userid);

        if ($reseller) {
            $resellerId = $reseller->client->id;
            $resellerName = $reseller->client->firstname.' '.$reseller->client->lastname;
            $displayInfo = $reseller->client->companyname ?: $resellerName;
            $message = Lang::T(self::CLIENT_BELONGS_MESSAGE);
            $adminDir = empty($customadminpath) ? "admin" : $customadminpath;
            return '<div class="alert alert-danger"><strong>'.$message.'</strong> <a href="' . $CONFIG['SystemURL'] . '/'.$adminDir.'/clientssummary.php?userid=' . $resellerId . '">' . $displayInfo . '</a></div>';
        }
        return '';
    }
}
