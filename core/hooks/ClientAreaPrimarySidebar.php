<?php

namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Helpers\DateFormatHelper;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\mgLibs\Helpers\DateFormatter;
use MGModule\ResellersCenter\models\whmcs\Ticket;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\repository\ResellersSettings;
use MGModule\ResellersCenter\repository\whmcs\Hostings;
use MGModule\ResellersCenter\repository\whmcs\Invoices;

class ClientAreaPrimarySidebar
{
    public $functions;
    public static $params;

    public function __construct()
    {
        $this->functions[10] = function($sidebar) {
            $this->checkUpgradePaymentStatus($sidebar);
        };

        if (!DateFormatHelper::changeDateFormatIsAllowed()) {
            return [];
        }

        $this->functions[30] = function($sidebar) {
            $this->formatDate($sidebar);
        };
    }

    public function checkUpgradePaymentStatus($sidebar)
    {
        if (basename(Server::get("SCRIPT_NAME")) != "upgrade.php") {
            return $sidebar;
        }
        $hostingId = Request::get("id");

        $hostingRepo = new Hostings();
        $hosting = $hostingRepo->find($hostingId);

        if ($hosting->order->invoice->status == Invoices::STATUS_UNPAID) {
            global $smartyvalues;
            $smartyvalues["overdueinvoice"] = true;
        }

    }

    public function formatDate($sidebar)
    {

        if (\Menu::primarySidebar()->getChild("Ticket Information")) {
            $isReseller       = ResellerHelper::isReseller(Session::get('uid'));
            $isResellerClient = (new ResellersClients())->getByRelid(Session::get('uid'))->exists;

            if ($isReseller || (!$isResellerClient && !ResellerHelper::getByCurrentURL()->exists) ) {
                return [];
            }
            $resellersClientsRepo = new ResellersClients();
            $clientId = $_SESSION["uid"];
            $resellerClient = $resellersClientsRepo->getByRelid($clientId);

            $format = (new ResellersSettings())->getSetting('dateFormat', $resellerClient->reseller_id, true);

            $dateFormatter = new DateFormatter();

            $ticketTid = Request::get('tid');
            $ticketCParameter = Request::get('c');
            $ticket = Ticket::select('date')->where("tid", $ticketTid)->where("c", $ticketCParameter)->first();
            global $whmcs;

            $date = $dateFormatter->format($ticket->date, $format, true);
            \Menu::primarySidebar()->getChild("Ticket Information")->getChild("Date Opened")->setLabel( $whmcs->get_lang("supportticketsubmitted").'<br>'.$date);
        }
    }

}