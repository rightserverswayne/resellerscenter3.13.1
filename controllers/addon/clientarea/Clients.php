<?php

namespace MGModule\ResellersCenter\controllers\addon\clientarea;

use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Exceptions\ConsolidatedSettingException;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings\EndClientConsolidatedInvoices;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;
use MGModule\ResellersCenter\models\ResellerClient;
use MGModule\ResellersCenter\models\whmcs\Ticket;
use MGModule\ResellersCenter\repository\CreditLines;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\repository\ResellersClientsSettings;
use MGModule\ResellersCenter\repository\ResellersServices;
use MGModule\ResellersCenter\repository\whmcs\Currencies;

use MGModule\ResellersCenter\core\datatable\DatatableDecorator as Datatable;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\Core\Whmcs\Clients\CustomFields;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\core\Session;
use MGModule\ResellersCenter\core\Redirect;
use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;

use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;
use MGModule\ResellersCenter\mgLibs\Lang;
use MGModule\ResellersCenter\core\Server;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\models\whmcs\CustomField;

/**
 * Description of Clients
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Clients extends AbstractController
{
    const SUSPENDED_STATUS = 'Suspended';
    const SUSPEND = 'suspend';

    public function __construct($input = [])
    {
        parent::__construct($input);

        if (ResellerHelper::isMakingOrderForClient() && Request::get("mg-action") != 'cleanAfterOrder') {
            Redirect::toPageWithQuery("clientarea.php", []);
        }
    }

    public function indexHTML()
    {
        $currencyRepo = new Currencies();

        $vars               = [];
        $vars["countries"]  = Whmcs::getCountries();
        $vars["currencies"] = $currencyRepo->getAvailableCurrencies();
        $vars["customFields"] = CustomFields::getAvailable();
        $reseller = ResellerHelper::getLogged();
        $vars["reseller"] = $reseller;

        return ['tpl'  => 'base', 'vars' => $vars];
    }

    public function getAssignedJSON()
    {
        $search = Request::get("term");

        $reseller = ResellerHelper::getLogged();
        $clients = $reseller->clients->getAssigned(100, $search);

        $result = [];
        foreach ($clients as $client) {
            $result[] = (array)$client;
        }

        return $result;
    }

    public function createJSON()
    {
        try {
            $data = Request::get("client");
            $reselleruid = Session::get("uid");
            $countryCode = Request::get("country-calling-code-phonenumber");
            
            $data["phonenumber"] = $countryCode ? "+{$countryCode}.".Request::get("phonenumber") : Request::get("phonenumber");

            if ($data['customfields']) {
                $data['customfields'] = $this->parseCustomFields($data['customfields']);
            }

            $emailVerificationTpl = Whmcs::isVersion('8.0') ?
                'preventSend_Email Address Verification' :
                'preventSend_Client Email Address Verification';

            //Prevent email sending
            Session::set("ResellerAddClient", $reselleruid);
            Session::set("preventSend_Client Signup Email", 1);
            Session::set($emailVerificationTpl, 1);

            $newClient   = new Client();
            $newClientId = $newClient->create($data);

            $data['client_id'] = $newClientId;
            $this->createCreditLine($data);

            //Relogin reseller - after clientadd WHMCS will login new client
            $reseller = new Reseller(null, $reselleruid);
            $reseller->client->login();

            EventManager::call("newClientCreated", $newClientId, $reseller->id);
            return array("success" => Lang::T('add', 'success'));
        }
        catch (\Exception $ex) {
            return array("validateError" => $ex->getMessage());
        }
    }

    public function deleteJSON()
    {
        global $CONFIG;
        $clientid    = Request::get("clientid");
        $reselleruid = Session::get("uid");

        $reseller = new Reseller(null, $reselleruid);

        if ($reseller->settings->private->hideDelete != 'on'
            && $reseller->settings->admin->hideDelete != 'on') {

            if ( !ResellerClient::where('client_id', $clientid)->where('reseller_id', $reseller->id)->first() ) {
                return ['error' => Lang::T('delete', 'failure')];
            }

            $reseller->clients->unassign($clientid);

            EventManager::call("unassingClient", $clientid);
            return array("success" => Lang::T('delete', 'success'));
        }

        $url = parse_url($CONFIG["SystemURL"]);
        Redirect::to($url["host"], $url["path"].'/index.php', array("m" => "ResellersCenter", "mg-page" => "clients"));
        exit;
    }

    /**
     * Add selected user id to session.
     * This works similar to WHMCS "Login as client" mechanism.
     */
    public function loginAsClientHTML($parameters = [])
    {
        global $CONFIG;
        $page = $CONFIG["DefaultToClientArea"] ? "clientarea.php" : "index.php";

        $this->loginAsClientAndRedirect($page, $parameters);
    }

    public function loginAndShowDomainHTML()
    {
        $domainId    = Request::get("domainid");
        $parameters = ['action'=>'domaindetails', 'id'=>$domainId];
        $page = 'clientarea.php';
        $this->loginAsClientAndRedirect($page, $parameters);
    }

    public function loginAndShowServiceHTML()
    {
        $serviceId    = Request::get("serviceid");
        $parameters = ['action'=>'productdetails', 'id'=>$serviceId];
        $page = 'clientarea.php';
        $this->loginAsClientAndRedirect($page, $parameters);
    }

    public function loginAndShowAddonHTML()
    {
        $addonId    = Request::get("addonid");
        $result = HostingAddon::select('hostingid')->where('id', $addonId)->first();
        $parameters = ['action'=>'productdetails', 'id'=>$result->hostingid."#tabAddons"];
        $page = 'clientarea.php';
        $this->loginAsClientAndRedirect($page, $parameters);
    }

    public function loginAndShowTicketHTML()
    {
        $ticketId    = Request::get("ticketid");
        $ticket = Ticket::where('id',$ticketId)->first();
        $parameters = ['tid'=>$ticket->tid, 'c'=>$ticket->c];
        $page = 'viewticket.php';
        $this->loginAsClientAndRedirect($page, $parameters);
    }

    protected function loginAsClientAndRedirect($page, $parameters = [])
    {
        global $CONFIG;

        $clientid    = Request::get("clientid");
        $reselleruid = Session::get("uid");

        //Login client
        $reseller = new Reseller(null, $reselleruid);

        if ($reseller->settings->admin->hideClientLogin || !$reseller->settings->admin->login) {
            return [
                'tpl'   => 'base',
                'vars'=>['error'=>Lang::T('loginAsClient', 'disabled')]
            ];
        }

        if (!ResellerClient::where('client_id',$clientid)->where('reseller_id',$reseller->id)->first()) {
            return [
                'tpl'   => 'base',
                'vars'=>['error'=>Lang::T('loginAsClient', 'failure')]
            ];
        }

        $client = new Client($clientid);
        $client->login();

        Session::set("loggedAsClient", $reseller->client_id);

        //Get Reseller domain for redirection
        $domain = rtrim($reseller->settings->private->domain,'/');
        $parsed = parse_url($CONFIG["SystemURL"]);
        $pageToRedirect = $parsed["path"] .DIRECTORY_SEPARATOR. $page;

        if (empty($domain) || !$reseller->settings->admin->cname) {
            $domain = Server::get("HTTP_HOST");
            $query  = array("resid" => $reseller->id);
        }
        else {
            $key   = Session::store();
            $query = array("rctoken" => $key);
        }

        if (!empty($parameters)) {
            $query = array_merge($query, $parameters);
        }

        EventManager::call("loggedAsClient", $reseller->id, $clientid);
        Redirect::to($domain, $pageToRedirect, $query, $parsed["scheme"]);
    }

    public function returnToRcJSON()
    {
        //Set back reseller userid in session
        $reselleruid = Session::get("loggedAsClient");

        $resellerClient = new Client($reselleruid);
        $resellerClient->login();

        //Clear session
        Session::clear("loggedAsClient");
        Session::clear("resid");
        Session::clear("Template");
        Session::clear("OrderFormTemplate");
        Session::clear("branded");
        Session::clear("orderdetails");
        Session::clear("cart");

        global $CONFIG;
        $url = parse_url($CONFIG["SystemURL"]);
        if ($url["host"] != Server::get("HTTP_HOST")) {
            Redirect::to($url["host"], $url["path"], array("m" => "ResellersCenter", "mg-page" => "clients", "mg-action" => "returnToRc", "json" => 1));
            exit;
        }

        Redirect::to($url["host"], $url["path"], array("m" => "ResellersCenter"));
    }

    /**
     * Create order for client by reseller.
     * Order will created during the normal order procedure but
     * in the last stage userid will be swap from reseller's userid to client's userid
     */
    public function createOrderHTML()
    {
        $clientid = Request::get("clientid");
        $reselleruid = Session::get("uid");
        
        $reseller = new Reseller(null, $reselleruid);

        global $CONFIG;
        $url = parse_url($CONFIG["SystemURL"]);

        if ($reseller->settings->private->order != 'on'
            && $reseller->settings->admin->order != 'on') {
            Redirect::to($url["host"], $url["path"].'/index.php', ["m" => "ResellersCenter", "mg-page" => "clients"]);
            exit;
        }

        if (!ResellerClient::where('client_id',$clientid)->where('reseller_id',$reseller->id)->first()) {
            return [
                'tpl'   => 'base',
                'vars'=> ['error'=>Lang::T('placeOrderForClient', 'failure')]
            ];
        }

        Session::set("makeOrderFor", $clientid);
        EventManager::call("makingOrderFor", $clientid);
        Redirect::to($url["host"], $url["path"] . "/cart.php");
    }

    /**
     *
     */
    public function cleanAfterOrderJSON()
    {
        Session::clear("resellerid");
        Session::clear("makeOrderFor");

        //Reload page so WHMCS could load correct client information
        Redirect::toPageWithQuery("index.php", array("m" => "ResellersCenter", "mg-page" => "clients"));
    }

    public function getAssignedForTableJSON()
    {
        $reseller  = ResellerHelper::getLogged();
        $dtRequest = Request::getDatatableRequest();

        $clients = new ResellersClients();
        $result  = $clients->getAssignedForTable($reseller->id, $dtRequest);

        $format = ["companyname" => ["default" => "-"]];
        
        $buttons = [];

        if (!$reseller->settings->admin->hideClientLogin
            && ($reseller->settings->admin->login == 'on'
            || $reseller->settings->private->login == 'on')) {
            $buttons[] = [
                "type"    => "only-icon",
                "class"   => "loginAsClientBtn btn-default",
                "data"    => ["clientid" => "client_id"],
                "icon"    => "fa fa-user",
                "tooltip" => Lang::T('table', 'loginAsClientInfo')
            ];
        }

        if ($reseller->settings->admin->order == 'on'
            || $reseller->settings->private->order == 'on') {
            $buttons[] = [
                "type"    => "only-icon",
                "class"   => "openAddOrderClient btn-warning",
                "data"    => ["clientid" => "client_id"],
                "icon"    => "fa fa-shopping-cart",
                "tooltip" => Lang::T('table', 'makeOrderInfo')
            ];
        }

        if (!$reseller->settings->admin->hideClientDetails) {
            $buttons[] = [
                "type"    => "only-icon",
                "class"   => "openDetailsClient btn-info",
                "data"    => ["clientid" => "client_id"],
                "icon"    => "fa fa-pencil-square-o",
                "tooltip" => Lang::T('table', 'detailsInfo')
            ];
        }

        if ($reseller->settings->private->hideDelete != 'on'
            && $reseller->settings->admin->hideDelete != 'on') {
            $buttons[] = [
                "type"    => "only-icon",
                "class"   => "openDeleteClient btn-danger",
                "data"    => ["clientid" => "client_id"],
                "icon"    => "fa fa-trash-o",
                "tooltip" => Lang::T('table', 'deleteInfo')
            ];
        }

        $datatable = new Datatable($format, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }

    public function checkIsAllowCreditLineJSON()
    {
        $reseller = ResellerHelper::getLogged();
        return ['result'   => $reseller->settings->admin->allowcreditline];
    }
    /*     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
     *  DETAILS
     */

    public function detailsHTML()
    {
        $clientid    = Request::get("cid");
        $reselleruid = Session::get("uid");

        $client   = new Client($clientid);
        $reseller = new Reseller(null, $reselleruid);
        
        if ($reseller->settings->admin->hideClientDetails) {
            return [
                'tpl'   => 'base',
                'vars'=>['error'=>Lang::T('detailsLoad', 'disabled')]
            ];
        }

        if (!ResellerClient::where('client_id',$clientid)->where('reseller_id',$reseller->id)->first()) {
            return [
                'tpl'   => 'base',
                'vars'=>['error'=>Lang::T('detailsLoad', 'failure')]
            ];
        }

        $gateways = Helper::getCustomGateways($reseller->id);

        //Check if client belongs to logged reseller
        if ($client->resellerClient->reseller_id != $reseller->id) {
            return array('tpl' => 'base', "error" => Lang::T('details', 'clientnotfound'));
        }

        $currencyRepo = new Currencies();
        $creditLineRepo = new CreditLines();
        $settingsRepo = new ResellersClients();

        return [
            'tpl'  => 'details/base',
            'vars' => [
                "whmcs8"            => Whmcs::isVersion('8.0'),
                "client"            => $client,
                "reseller"          => ResellerHelper::getLogged(),
                "countries"         => Whmcs::getCountries(),
                "currencies"        => $currencyRepo->getAvailableCurrencies(),
                "gateways"          => $gateways,
                "taxes"             => ["tax1" => $client->tax, "tax2" => $client->tax2],
                "assetsURL"         => \MGModule\ResellersCenter\Addon::I()->getAssetsURL(),
                "customFields"      => CustomFields::getAvailable(),
                "creditLine"        => $creditLineRepo->getByClientId($client->id),
                "clientSettings"    => (object)$settingsRepo->getSettingsByClientId($client->id),
                "endClientConsolidatedInvoices" => SettingsManager::getSettingFromReseller($reseller, EndClientConsolidatedInvoices::NAME)
            ]
        ];
    }
    
    public function resetPasswordJSON()
    {
        try {
            $session = null;
            $clientid = Request::get("clientid");

            $reselleruid = Session::get("uid");
            $reseller = new Reseller(null, $reselleruid);

            if ( !ResellerClient::where('client_id', $clientid)->where('reseller_id', $reseller->id)->first() ) {
                return ['error' => Lang::T('resetPassword', 'failure')];
            }

            if (Whmcs::isVersion('8.0')) {
                /* Need to save because of WhmcsAPI is clearing it in WHMCS 8 */
                $reseller = \MGModule\ResellersCenter\Core\Helpers\Reseller::getLogged();
                Session::set('rcResellerId', $reseller->id);
                $session = Session::store();
            }

            $client = new Client($clientid);
            $client->resetPassword($session);

            return ["success" => Lang::T('resetpw', 'success')];
        } 
        catch (\Exception $ex) {
            return ["error" => Lang::T('resetpw', $ex->getMessage())];
        }
    }

    public function updateProfileJSON()
    {
        try {
            $data = Request::get("client");
            $dataSettings = Request::get("clientSettings");

            $data["phonenumber"]  = "+".Request::get("country-calling-code-phonenumber").".".Request::get("phonenumber");
            $data["customfields"] = base64_encode(serialize($data["customfields"]));
            $client = new Client($data["id"]);
            $client->update($data);

            if ($data["creditlinelimit"] != null) {
                $parameters['client_id'] = $data["id"];
                $parameters['reseller_id'] = $client->getReseller()->id;
                $parameters['limit'] = $data["creditlinelimit"];
                $repository = new CreditLines();
                $repository->updateOrCreate($parameters);
            }

            $settingsRepo = new ResellersClientsSettings();
            $settingsRepo->updateSettingsByClientId($data["id"], $dataSettings);

            EventManager::call("clientProfileUpdated", $data["id"], $client->resellerClient->id);
            return ["success" => Lang::T('update', 'success')];
        } catch (ConsolidatedSettingException $e) {
            return array("error" => Lang::absoluteT('consolidatedInvoices','errorMessages', $e->getMessage()));
        } catch (\Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function termianteServiceJSON()
    {
        $relid = Request::get("relid");

        $repo    = new ResellersServices();
        $service = $repo->find($relid);

        try {
            if ($service->type == ResellersServices::TYPE_HOSTING) {
                if (!empty($service->hosting->product->servertype)) {
                    WhmcsAPI::request("ModuleTerminate", array("accountid" => $service->relid, "serviceid" => $service->relid));
                }
                else {
                    WhmcsAPI::request("UpdateClientProduct", array(
                        "serviceid"       => $service->relid,
                        "status"          => "Terminated",
                        "terminationDate" => date("Y-m-d")
                    ));
                }
            }
            elseif ($service->type == ResellersServices::TYPE_ADDON) {
                WhmcsAPI::request("UpdateClientAddon", array(
                    "id"              => $service->relid,
                    "status"          => "Terminated",
                    "terminationDate" => date("Y-m-d")
                ));
            }

            EventManager::call("serviceTermianted", $service->relid, $service->reseller->id);
        }
        catch (\Exception $ex) {
            return ["error" => $ex->getMessage()];
        }

        return ["success" => Lang::T('terminate', 'success')];
    }

    public function getServicesForTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();
        $clientid  = Request::get("clientid");

        $reseller = ResellerHelper::getLogged();
        $result = $reseller->hosting->getForTable($dtRequest, $clientid);

        $buttons[] =
                ["type"    => "only-icon",
                "class"   => "openDeleteService btn-danger",
                "data"    => array("serviceid" => "id"),
                "icon"    => "fa fa-trash-o",
                "tooltip" => Lang::T('services', 'table', 'deleteInfo')];

        if ($reseller->settings->admin->suspend == 'on' || $reseller->settings->private->suspend == 'on') {
            $buttons[] = [
                "type"    => "only-icon",
                "class"   => "openUnsuspendService btn-info",
                "data"    => ["hosting_id" => "hosting_id"],
                "icon"    => "fa fa-unlock",
                "tooltip" => Lang::T('table', 'unsuspendService'),
                "if"      => [["status", "==", self::SUSPENDED_STATUS]]
            ];
            $buttons[] = [
                "type"    => "only-iconn",
                "class"   => "openSuspendService btn-warning",
                "data"    => ["hosting_id" => "hosting_id"],
                "icon"    => "fa fa-lock",
                "tooltip" => Lang::T('table', 'suspendService'),
                "if"      => [["status", "!=", self::SUSPENDED_STATUS]]
            ];
        }

        $datatable = new Datatable(null, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }

    public function suspendJSON()
    {
        global $CONFIG;

        $serviceId = Request::get("relid");
        $state = Request::get("state");

        $reseller = ResellerHelper::getLogged();

        if ($reseller->settings->private->suspend == 'on' || $reseller->settings->admin->suspend == 'on') {

            $resellersServices = new ResellersServices();
            $command = $state == self::SUSPEND ? 'ModuleSuspend' : 'ModuleUnsuspend';
            $postData = [
                'serviceid' => $serviceId,
                'suspendreason' => 'Abuse',
            ];

            try {
                if (!$resellersServices->serviceBelongsToReseller($serviceId, $reseller->id)) {
                    throw new \Exception(Lang::T('serviceNotBelongsToReseller'));
                }
                WhmcsAPI::request($command, $postData);;
            } catch (\Exception $ex) {
                return ["error" => $ex->getMessage()];
            }

            return ["success" => Lang::T($state, 'success')];//$this->getSuspendMessageByState($state);
        }

        $url = parse_url($CONFIG["SystemURL"]);
        Redirect::to($url["host"], $url["path"].'/index.php', ["m" => "ResellersCenter", "mg-page" => "clients"]);
        exit;
    }

    public function getAddonsForTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();
        $clientid  = Request::get("clientid");
        
        $reseller  = ResellerHelper::getLogged();
        $result = $reseller->addons->getForTable($dtRequest, $clientid);

        $buttons = array(
            array(
                "type"    => "only-icon",
                "class"   => "openDeleteAddon btn-danger",
                "data"    => array("addonid" => "id"),
                "icon"    => "fa fa-trash-o",
                "tooltip" => Lang::T('services', 'table', 'deleteInfo'))
        );

        $datatable = new Datatable(null, $buttons);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }

    public function getDomainsForTableJSON()
    {
        $dtRequest = Request::getDatatableRequest();
        $clientid  = Request::get("clientid");
        
        $reseller  = ResellerHelper::getLogged();
        $result = $reseller->domains->getForTable($dtRequest, $clientid);

        $datatable = new Datatable(null);
        $datatable->parseData($result["data"], $result["displayAmount"], $result["totalAmount"]);

        return $datatable->getResult();
    }

    private function parseCustomFields( $customFieldsValues )
    {
        $customFieldsTypes = CustomField::whereIn('id', array_keys($customFieldsValues))
                                        ->where('type', '=', 'client')
                                        ->pluck('fieldtype','id')
                                        ->toArray();

        foreach ( $customFieldsValues as $id => &$value ) {
            if ( isset($customFieldsTypes[$id]) && $customFieldsTypes[$id] === 'tickbox' ) {
                $value = $value ? 'on' : '';
            }
        }

        return base64_encode(serialize($customFieldsValues));
    }

    private function createCreditLine($data)
    {
        if ($data['client_id']) {
            $parameters['client_id'] = $data['client_id'];
            $parameters['reseller_id'] = ResellerHelper::getLogged()->id;
            $parameters['limit'] = $data["creditlinelimit"];
            $repository = new CreditLines();
            $repository->updateOrCreate($parameters);
        }
    }
}