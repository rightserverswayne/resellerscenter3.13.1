<?php
namespace MGModule\ResellersCenter\Core\Helpers;

use MGModule\ResellersCenter\repository\Resellers;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller as ResellerObj;
use MGModule\ResellersCenter\Core\Whmcs\Invoices\InvoiceItem;
use MGModule\ResellersCenter\repository\ResellersClients as ResellerClientsRepo;

use MGModule\ResellersCenter\Core\Server;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\repository\whmcs\Invoices;

/**
 * Description of Reseller.php
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Reseller
{
    /**
     * Reseller object
     *
     * @var \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller
     */
    public static $reseller = null;

    /**
     * @var bool
     */
    public static $searched = false;

    /**
     * This function will return reseller object only if:
     * Current domain is reseller shop domain
     * or
     * reseller is making order for his client
     *
     * @return \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller
     */
    public static function getCurrent()
    {
        if (Reseller::isMakingOrderForClient()) {
            /**
             * Use resellerid key if is set (when reseller is making order for a client).
             * on the last step of order process uid is replaced with id of a reseller's
             * client and reseller userid is stored in resellerid
             */
            $uid = empty(Session::get("resellerid")) ? Session::get("uid") : Session::get("resellerid");
            $reseller = new ResellerObj(null, $uid);
        } else {
            $reseller = Reseller::getByCurrentURL();
        }

        return $reseller;
    }

    /**
     * Return reseller based on current URL.
     * Check if domain belongs to any reseller
     * If not search for reseller using ID, if provided in URL
     *
     * @return type
     */
    public static function getByCurrentURL()
    {
        $isCron = Server::isRunByCron();
       
        //Check if we have already loaded reseller
        if (!$isCron && self::$searched) {
            return self::$reseller;
        }

        $domain = Server::get("HTTP_HOST");
        $repo = new Resellers();
        $resellerModel = $repo->getResellerByDomainName($domain);

        //If we can find reseller by domain
        if (empty($resellerModel)) {
            $resid = Request::get("resid") ?: Session::get("resid");
            if ($resid) {
                //Remove client from session if he changed stores
                if (Session::get("resid") && $resid != Session::get("resid")) {
                    Session::clear("uid");
                    Session::clear("upw");

                    setcookie('WHMCSUser', null, -1, '/');
                }

                Session::set("resid", $resid);
            }

            $resellerModel = $repo->find($resid);
        }

        //Save in static variable (not in cron)
        if (!$isCron) {
            self::$reseller = new ResellerObj($resellerModel->id);
            self::$searched = true;
            return self::$reseller;
        }

        return new ResellerObj($resellerModel->id);
    }

    /**
     * Get currently logged reseller.
     *
     * @since 3.0.0
     * @return \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller
     * @throws \Exception
     */
    public static function getLogged()
    {
        $clientid = Session::get("uid");
        $reseller = new ResellerObj(null, $clientid);

        return $reseller;
    }

    /**
     * Check if reseler is making order for his client
     *
     * @return boolean
     */
    public static function isMakingOrderForClient()
    {
        if(empty(Session::get("makeOrderFor")))
        {
            return false;
        }

        return true;
    }

    /**
     * Check if provided client is Reseller and his account is enabled by admin
     *
     * @since 3.0.0
     * @param type $clientid
     * @return boolean
     */
    public static function isReseller($clientid)
    {
        $reseller = new ResellerObj(null, $clientid);
        if (!$reseller->exists || !$reseller->settings->admin->status) {
            return false;
        }

        return true;
    }


    /**
     * @param $items
     * @return \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller
     */
    public static function findByInvoiceItems($items)
    {
        $result = null;
        foreach ($items as $raw) {
            $raw = new InvoiceItem($raw);
            $result = $raw->getReseller();

            if ($result->exists) {
                break;
            }
        }

        return $result;
    }

    public static function createById($id)
    {
        $resellerRepo = new Resellers();
        return new ResellerObj($resellerRepo->find($id));
    }

    public static function getResellerObjectByHisClientId($clientId)
    {
        $repo = new ResellerClientsRepo();
        $reseller = $repo->getResellerByClientId($clientId);
        return new ResellerObj($reseller);
    }

    public static function getByClientId($clientId)
    {
        return new ResellerObj(null, $clientId);
    }

    public static function hasResellerRelatedInvoices(ResellerObj $reseller):bool
    {
        if ($reseller->settings->admin->resellerInvoice) {
            return $reseller->RCInvoices->count() > 0;
        } else {
            $invoicesRepo = new Invoices();
            $relatedInvoicesIds = $invoicesRepo->getAvailableInvoices($reseller->id);
            return !empty($relatedInvoicesIds);
        }
    }

}