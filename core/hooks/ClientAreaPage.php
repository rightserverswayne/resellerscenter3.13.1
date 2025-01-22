<?php

namespace MGModule\ResellersCenter\core\hooks;

use DOMDocument;
use MGModule\ResellersCenter\Addon;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Resources\gateways\PaymentGateway;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\gateways\DeferredPayments\DeferredPayments;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\libs\CreditLine\Services\CreditLineService;
use MGModule\ResellersCenter\libs\ResellerClientsCases\CasesFactory;
use MGModule\ResellersCenter\mgLibs\Helpers\DateFormatter;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\repository\ResellersSettings;
use MGModule\ResellersCenter\repository\whmcs\Hostings;
use MGModule\ResellersCenter\repository\whmcs\Domains;
use MGModule\ResellersCenter\repository\whmcs\DomainPricing;
use MGModule\ResellersCenter\repository\whmcs\Invoices;

use MGModule\ResellersCenter\repository\Invoices as RCInvoicesRepo;
use MGModule\ResellersCenter\repository\ResellersTickets;
use MGModule\ResellersCenter\repository\ResellersServices;

use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\Core\Whmcs\Currencies\Currency;
use MGModule\ResellersCenter\Core\Resources\Invoices\Invoice as ResellersCenterInvoice;
use MGModule\ResellersCenter\Core\Resources\Promotions\Promotion as ResellerPromotion;

use MGModule\ResellersCenter\core\cart\Order\View;

use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\core\helpers\CartHelper;
use MGModule\ResellersCenter\core\helpers\CartDomains;
use MGModule\ResellersCenter\core\helpers\DomainHelper;
use MGModule\ResellersCenter\Core\Helpers\ClientAreaHelper;
use MGModule\ResellersCenter\Core\Helpers\Urls\Url;

use MGModule\ResellersCenter\core\Server;
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\core\Session;
use MGModule\ResellersCenter\core\Redirect;
use MGModule\ResellersCenter\repository\whmcs\Tickets;
use MGModule\ResellersCenter\repository\whmcs\TicketDepartments;

/**
 * Description of ClientAreaPage
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ClientAreaPage
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     * 
     * @var array 
     */
    public $functions;
    
    /**
     * Container for hook params
     * 
     * @var type 
     */
    public static $params;
    
    /**
     * Assign anonymous function
     */
    public function __construct()
    {
        $this->functions[4] = function($params) {
            $this->blockPages($params);
        };

        $this->functions[5] = function($params) {
            $this->checkIsAddonsPage($params);
        };

        $this->functions[6] = function($params) {
            $this->redirectToNotEmptyGroupIfNecessary($params);
        };

        $this->functions[10] = function($params) {
            self::$params = $this->setShopBranding($params);
        };

        $this->functions[20] = function($params) {
            self::$params = $this->setBrandingForServices(self::$params);
        };

        $this->functions[30] = function($params) {
            self::$params = $this->setBrandingForDomains(self::$params);
        };

        $this->functions[40] = function($params) {
            self::$params = $this->setBrandingForTickets(self::$params);
        };

        $this->functions[60] = function($params) {
            self::$params = $this->setBrandingForInvoices(self::$params);
        };

        $this->functions[70] = function($params) {
            self::$params = $this->setBrandingForSupport(self::$params);
        };

        $this->functions[75] = function($params) {
            self::$params = $this->setBrandingForKnowledgebase(self::$params);
        };

        $this->functions[80] = function($params) {
            self::$params = $this->setPricingForDomainsRenewal(self::$params);
        };

        $this->functions[90] = function($params) {
            self::$params = $this->removeDisabledGateways(self::$params);
        };

        $this->functions[100] = function($params) {
            self::$params = $this->setPricesForUpgrade(self::$params);
        };

        $this->functions[110] = function($params) {
            self::$params = $this->setResellerVarInTemplate(self::$params);
        };

        $this->functions[120] = function($params) {
            self::$params = $this->setAfterUpgradeBranding(self::$params);
        };

        $this->functions[130] = function($params) {
            self::$params = $this->validateUpgradePromotionCode(self::$params);
        };

        $this->functions[140] = function($params) {
            self::$params = $this->setDomainPricingView(self::$params);
        };

        $this->functions[150] = function($params) {
            self::$params = $this->overrideProductGroupsView(self::$params);
        };

        $this->functions[160] = function($params) {
            self::$params = $this->setInvoicesDateFormat(self::$params);
        };

        $this->functions[170] = function($params) {
            self::$params = $this->setServicesDateFormat(self::$params);
        };

        $this->functions[180] = function($params) {
            self::$params = $this->manageDeferredGateway(self::$params);
        };

        $this->functions[200] = function($params)
        {
            //Fix for WHMCS 7.2.1
            global $smartyvalues;
            $smartyvalues = self::$params;

            return self::$params;
        };
    }
    
    /**
     * Set WHMCS CA branding. 
     * Only if client is logged to Reseller Shop
     * This function changes only look of the shop, not content
     * 
     * @param type $params
     * @return type
     */
    public function setShopBranding($params)
    {
        global $CONFIG;

        //Check if we are in reseller store or reseller is making order for client
        $reseller = ResellerHelper::getCurrent();
        if ($reseller->exists && $reseller->settings->admin->branding) {
            if ($reseller->settings->private->tos) {
                $params["accepttos"] = $params["acceptTOS"] = "on";
                $params["tosurl"]    = $reseller->settings->private->tos;
            } else {
                $params["accepttos"] = $params["acceptTOS"] = "";
            }
        }

        //Check if we are in reseller store
        $reseller = ResellerHelper::getByCurrentURL();
        if (!$reseller->exists) {
            return $params;
        }


        if ($reseller->settings->admin->branding) {
            //Get reseller private settings
            $settings = $reseller->settings->private;

            //Templates
            if ($settings->whmcsTemplate) {
                $params["template"] = $settings->whmcsTemplate;
                Session::set("Template", $settings->whmcsTemplate);
            }

            //Carts without checkout tpl will use standard_cart template at checkout page
            $cartsWithoutCheckoutTpl = ["cloud_slider", "premium_comparison", "pure_comparison", "supreme_comparison", "universal_slider"];
            if ($settings->orderTemplate && !(Request::get("a") == "checkout" && in_array($settings->orderTemplate, $cartsWithoutCheckoutTpl))) {
                $params["carttpl"] = $settings->orderTemplate;
                Session::set("OrderFormTemplate", $settings->orderTemplate);
            }

            //Set system URL
            if ($settings->domain && $reseller->settings->admin->cname) {
                $params["systemurl"] = $params["systemsslurl"] = Server::getSystemURL($settings->domain);
                
                global $CONFIG;     
                if ($params["breadcrumb"]) {
                    foreach ($params["breadcrumb"] as &$breadcrumb) {
                        $parsed = parse_url($CONFIG["SystemURL"]);
                        $newUrl = str_replace($parsed["host"], $settings->domain, $breadcrumb["link"]);
                        $breadcrumb["link"] = $newUrl;
                    }
                }
            }

            //Logo
            if ($settings->logo) {
                $pathToLogo = ClientAreaHelper::getLogoPath().$settings->logo;
                $whmcsURL = parse_url($CONFIG["SystemURL"]);

                if ($reseller->settings->admin->cname && $settings->domain) {
                    $logoUrl = $whmcsURL['scheme'] . '://' . $settings->domain . rtrim($whmcsURL['path'], '/' ) .'/'. $pathToLogo;
                } else {
                    $logoUrl = "{$CONFIG["SystemURL"]}/{$pathToLogo}";
                }

                $params["RCLogo"] = $logoUrl;
                $params["RCInvoiceLogo"] = $logoUrl;
            }

            //InvoiceLogo
            if ($settings->showInvoiceLogo && $settings->invoiceLogo) {
                $pathToLogo = ClientAreaHelper::getLogoPath().$settings->invoiceLogo;
                $whmcsURL = parse_url($CONFIG["SystemURL"]);

                if ($reseller->settings->admin->cname && $settings->domain) {
                    $logoUrl = $whmcsURL['scheme'] . '://' . $settings->domain . rtrim($whmcsURL['path'], '/' ) .'/'. $pathToLogo;
                } else {
                    $logoUrl = "{$CONFIG["SystemURL"]}/{$pathToLogo}";
                }

                $params["RCInvoiceLogo"] = $logoUrl;
            }

            $params["todaysdate"] = 'DD/MM/YYYY';
        
            //Company name
            if ($settings->companyName) {
                $params["companyname"] = $settings->companyName;
                if (Url::isOnPage(Url::ANNOUNCEMENTS)) {
                    global $whmcs;
                    $params["tagline"] = $whmcs->get_lang("allthelatest") . " {$settings->companyName}";
                }
            }
        }
        
        //If this is first connection we have to refresh
        if (!Session::get("branded")) {
            Session::set("branded", true);
            Redirect::refresh();
        }
        
        return $params;
    }
    
    /**
     * Remove client's Hosting that were not bought in reseller shop
     * 
     * @param type $params
     * @return type
     */
    public function setBrandingForServices($params)
    {
        if (ResellerHelper::isReseller(Session::get("uid"))) {
            return $params;
        }

        $reseller = ResellerHelper::getByCurrentURL();
        if ((!$reseller->exists || ClientAreaHelper::isCartPage())) {
            //Skip filters and show all services
            if (!Addon::I()->configuration()->adminStoreServiceFilter) {
                return $params;
            }

            $admin = 1;
        } else {
            $admin = 0;
        }

        $repo   = new ResellersServices();
        $models = $repo->getServicesByClientId(Session::get("uid"), ResellersServices::TYPE_HOSTING);

        if ($params["relatedservices"]) {
            foreach ($params["relatedservices"] as $key => $service) {
                $model = $models->where("relid", substr($service["id"], 1))->first();
                if (($admin && $model->exists) || (!$admin && !$model->exists)) {
                    unset($params["relatedservices"][$key]);
                }
            }
        }

        if ($params["panels"]["Active Products/Services"]) {
            foreach ($params["panels"]["Active Products/Services"] as $key => $panelService) {
                $url = $panelService->getUri() ?: $this->getUriFromLabel($panelService->getLabel());
                if(empty($url))
                {
                    continue;
                }
                $relid = Helper::getParamFromURL("id", $url);
                $model = $models->where("relid", $relid)->first();

                if (($admin && $model->exists) || (!$admin && !$model->exists)) {
                    unset($params["panels"]["Active Products/Services"][$key]);
                    continue;
                }
            }
        }

        $hostings = new Hostings();
        $params["clientsstats"]["productsnumactive"] = $hostings->getCount($params["clientsdetails"]["userid"],Hostings::STATUS_ACTIVE, $admin);

        //Set correct numbers in sidebar
        $counters = array('Active' => 0, 'Pending' => 0, 'Suspended' => 0,'Terminated' => 0, 'Cancelled'  => 0, "Fraud" => 0);
        if ($params["relatedservices"]) {
            foreach ($params["relatedservices"] as $service) {
                $counters[$service["status"]]++;
            }
        }

        //Set badges on sidebar
        $sidebar = $params["primarySidebar"]->getChildren();
        if (!empty($sidebar["My Services Status Filter"])) {
            $sidebarItems = $sidebar["My Services Status Filter"]->getChildren();
            foreach ($counters as $status => $count) {
                if (isset($sidebarItems[$status])) {
                    $sidebarItems[$status]->setBadge($count);
                }
            }
        }

        $settingsRepo = new ResellersSettings();
        $resellerInvoice = $settingsRepo->getSetting('resellerInvoice', $reseller->id);

        //check ResellerInvoice option for unpaid invoice
        if ($params['unpaidInvoice'] && $resellerInvoice) {
            $resellerInvoicesRepo = new RCInvoicesRepo();
            $resellerInvoice = $resellerInvoicesRepo->getByWHMCSInvoiceId($params['unpaidInvoice']);

            if ($resellerInvoice->status == 'Paid') {
                $params['unpaidInvoice'] = null;
            }
        }

        return $params;
    }

    private function getUriFromLabel($labelHTML)
    {
        $dom = new DOMDocument('1.0','UTF-8');
        $dom->loadHTML(preg_replace('/[&]/', '&amp;', $labelHTML));

        $node = $dom->getElementsByTagName('div')->item(0);
        if(!$node)
        {
            return "";
        }

        return $node->getAttribute( 'data-href' );
    }

    /**
     * Remove client's Domains that were not bought in reseller shop
     *
     * @param type $params
     * @return type
     */
    public function setBrandingForDomains($params)
    {
        if (ResellerHelper::isReseller(Session::get("uid"))) {
            return $params;
        }

        $reseller = ResellerHelper::getByCurrentURL();
        if (!$reseller->exists || ClientAreaHelper::isCartPage()) {
            //Skip filters and show all services
            if (!Addon::I()->configuration()->adminStoreServiceFilter) {
                return $params;
            }

            $admin = 1;
        } else {
            if (Request::get("action") == "bulkdomain") {
                return $params;
            }

            $admin = 0;
        }

        //Set correct numbers in sidebar - WHMCS translate array keys...
        $counters = [
            'clientareaactive'                  => 0,
            'clientareaexpired'                 => 0,
            'clientareacancelled'               => 0,
            'clientareafraud'                   => 0,
            'clientareapending'                 => 0,
            'clientareapendingtransfer'         => 0,
            'domainsExpiringInTheNext30Days'    => 0,
            'domainsExpiringInTheNext90Days'    => 0,
            'domainsExpiringInTheNext180Days'   => 0,
            'domainsExpiringInMoreThan180Days'  => 0,
        ];

        $repo = new ResellersServices();
        $models = $repo->getServicesByClientId(Session::get("uid"), ResellersServices::TYPE_DOMAIN);
        if ($params["domains"]) {
            foreach ($params["domains"] as $key => $domain) {
                $domain = (array)$domain;
                //Remove domains from table view
                $domainId = is_array($domain) ? $domain['id'] : $domain->id;
                $domainStatus = is_array($domain) ? $domain['status'] : $domain->status;

                $model = $models->where("relid", $domainId)->first();
                if (($admin && $model->exists) || (!$admin && !$model->exists)) {
                    unset($params["domains"][$key]);
                    continue;
                }

                //Add badges to sidebar
                $status = strtolower(str_replace(" ", "", $domainStatus));
                $counters["clientarea{$status}"]++;

                if ($domainStatus === Domains::STATUS_ACTIVE) {
                    $domainNormalisedExpiryDate = is_array($domain) ? $domain['normalisedExpiryDate'] : $domain->normalisedExpiryDate;

                    if ($domainNormalisedExpiryDate == "0000-00-00") {
                        $counters['domainsExpiringInMoreThan180Days']++;
                    } elseif (strtotime($domainNormalisedExpiryDate) < strtotime("+30 days")) {
                        $counters['domainsExpiringInTheNext30Days']++;
                    } elseif (strtotime($domainNormalisedExpiryDate) < strtotime("+90 days")) {
                        $counters['domainsExpiringInTheNext90Days']++;
                    } elseif (strtotime($domainNormalisedExpiryDate) < strtotime("+180 days")) {
                        $counters['domainsExpiringInTheNext180Days']++;
                    } elseif (strtotime($domainNormalisedExpiryDate) > strtotime("+180 days")) {
                        $counters['domainsExpiringInMoreThan180Days']++;
                    }
                }
            }
        }

        $repo = new Domains();
        $activeDomains = $repo->getCount($params["clientsdetails"]["userid"], Domains::STATUS_ACTIVE, $admin);
        $params["clientsstats"]["numactivedomains"] = $activeDomains;

        if ($params["primarySidebar"]["My Domains Status Filter"]) {
            $sidebarItems = $params["primarySidebar"]["My Domains Status Filter"]->getChildren();
            foreach ($counters as $status => $count) {
                if (isset($sidebarItems[$status])) {
                    $sidebarItems[$status]->setBadge($count);
                }
            }
        }

        return $params;
    }

    /**
     * Removes ticket that are not opened by reseller site
     * Also removes departments that are disabled by admin for specified reseller
     *
     * @return type
     */
    public function setBrandingForTickets($params)
    {
        if (ResellerHelper::isReseller(Session::get("uid"))) {
            return $params;
        }

        $reseller = ResellerHelper::getByCurrentURL();

        if (!$reseller->exists) {
            //Skip filters and show all services
            if (!Addon::I()->configuration()->adminStoreServiceFilter) {
                return $params;
            }

            $admin = 1;
        } else {
            //display even hidden departments if are enabled in reseller settings
            $ticketDepartments  = new TicketDepartments();            
            $departments        = $ticketDepartments->getAllDepartmentsSorted(ClientAreaHelper::isClientLogged());

            $params["departments"] = [];

            $departmentsIds = $reseller->settings->admin->ticketDeptids ?: [];
            
            foreach ($departments as $department) {
                if (in_array($department->id, $departmentsIds)) {
                    $params["departments"][]    = [
                        "id"            => $department->id,
                        "name"          => $department->name,
                        "description"   => $department->description,
                    ];
                }
            }

            $admin = 0;
        }

        //Filter the tickets list
        $repo   = new ResellersTickets();
        $resellersClients = new ResellersClients();
        $resellerId = $resellersClients->getResellerIdByHisClientId($params['client']->id);

        $resellerTicketIds = $repo->getResellerTicketIdsByClientAndReseller($resellerId, Session::get("uid"));

        $counters = [Tickets::STATUS_OPEN => 0, Tickets::STATUS_ANSWERED => 0, Tickets::STATUS_CUSTOMER_REPLY => 0, Tickets::STATUS_CLOSED => 0];

        if ($params["tickets"]) {
            foreach ($params["tickets"] as $key => $ticketCart) {
                if (($admin && in_array($ticketCart["id"], $resellerTicketIds)) || (!$admin && !in_array($ticketCart["id"], $resellerTicketIds))) {
                    unset($params["tickets"][$key]);
                } else {
                    $counters[strip_tags($ticketCart["status"])]++;
                }
            }
        }

        $resellerTicketTids = $repo->getResellerTicketTidsByClientAndReseller($resellerId, Session::get("uid"));

        //Remove from recent tickets
        if(!empty($params["secondarySidebar"]["Recent Tickets"]))
        {
            $sidebar = $params["secondarySidebar"];

            foreach($sidebar["Recent Tickets"] as $key => $recent)
            {
                $tid    = substr($recent->getName(), strpos($recent->getName(), "#")+1);

                if (($admin && in_array($tid, $resellerTicketTids)) || (!$admin && !in_array($tid, $resellerTicketTids))) {
                    unset($sidebar["Recent Tickets"][$key]);
                }
            }
        }

        $repo = new Tickets();

        //Recent Support Ticket in panel
        if($params["panels"]["Recent Support Tickets"])
        {
            foreach ($params["panels"]["Recent Support Tickets"] as $key => $ticket) {
                $tid = Helper::getParamFromURL("tid", $ticket->getUri());

                if (($admin && in_array($tid, $resellerTicketTids)) || (!$admin && !in_array($tid, $resellerTicketTids))) {
                    unset($params["panels"]["Recent Support Tickets"][$key]);
                }
            }
        }

        //Get number of active ticket in panel view
        $ticketsCount = $repo->getCount($params["clientsdetails"]["userid"], 1, $admin);
        $params["clientsstats"]["numactivetickets"] = $ticketsCount;

        $sidebar = $params["primarySidebar"]->getChildren();
        if(!empty($sidebar["Ticket List Status Filter"]))
        {
            $sidebarItems = $sidebar["Ticket List Status Filter"]->getChildren();
            foreach($counters as $status => $count)
            {
                if(isset($sidebarItems[$status])) {
                    $sidebarItems[$status]->setBadge($count);
                }
            }
        }

        if(!empty($params["secondarySidebar"]["Recent Tickets"]))
        {
            //If there is nothing left in panel
            if(! $params["secondarySidebar"]["Recent Tickets"]->hasChildren())
            {
                global $whmcs;
                $lang = $whmcs->get_lang('clientHomePanels');
                $params["secondarySidebar"]["Recent Tickets"]->setBodyHtml('<p>'.$lang["activeProductsServicesNone"].'</p>');
            }
        }

        if(!empty($params["panels"]["Recent Support Tickets"]))
        {
            if(! $params["panels"]["Recent Support Tickets"]->hasChildren())
            {
                unset($params["panels"]["Recent Support Tickets"]);
            }
        }


        return $params;
    }

    /**
     * This hook work only on reseller domain
     * Remove client's Invoices that were not bought in reseller shop
     *
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function setBrandingForInvoices($params)
    {
        global $whmcs;

        $userId = Session::get('uid');
        $isReseller       = ResellerHelper::isReseller($userId);
        $isResellerClient = (new ResellersClients())->getByRelid($userId)->exists;

        if ($isReseller || !$isResellerClient) {
            return $params;
        }

        $reseller = ResellerHelper::getByCurrentURL();

        if ($reseller->settings->admin->disableEndClientInvoices) {
            $params['invoices'] = [];
            $params['clientsstats']['numdueinvoices']               = 0;
            $params['clientsstats']['numoverdueinvoices']           = 0;
            $params['clientsstats']['numunpaidinvoices']            = 0;
            $params['clientsstats']['numpaidinvoices']              = 0;
            $params['clientsstats']['numcancelledinvoices']         = 0;
            $params['clientsstats']['numrefundedinvoices']          = 0;
            $params['clientsstats']['numcollectionsinvoices']       = 0;
            $params['clientsstats']['numpaymentpendinginvoices']    = 0;

            return $params;
        }

        $resellerClientCase = CasesFactory::getByCurrentURL();

        $counters = $resellerClientCase->getInvoicesCounters();
        $balance = $resellerClientCase->getUnpaidInvoicesBalance();
        $overDueCounter = $resellerClientCase->getUnpaidInvoicesOverdueCount();
        $overDueBalance = $resellerClientCase->getUnpaidInvoicesOverdueBalance();

        //My Invoices Summary
        $sidebar = $params['primarySidebar']->getChildren();
        if ( !empty($sidebar['My Invoices Summary']) && $counters[Invoices::STATUS_UNPAID]> 0) {
            $label = $counters[Invoices::STATUS_UNPAID]. ' ' . $whmcs->get_lang('invoicesdue');
            $sidebar["My Invoices Summary"]->setLabel($label);

            $body = sprintf($whmcs->get_lang("invoicesduemsg"), $counters[Invoices::STATUS_UNPAID], formatCurrency($balance));
            $sidebar["My Invoices Summary"]->setBodyHtml($body);

            if ($counters[Invoices::STATUS_UNPAID] > 1) {
                //Add Pay All button only if Mass Payment is available
                if($params["secondarySidebar"]["Billing"]["Mass Payment"])
                {
                    $sidebar["My Invoices Summary"]->setFooterHtml("<div class='col-xs- col-button-left'><a href='clientarea.php?action=masspay&all=true' class='btn btn-success btn-sm btn-block'\{\$massPayDisabled\}><i class='fa fa-check-circle'></i> {$whmcs->get_lang('masspayall')}</a></div>");
                }

                $panelClass = $counters[Invoices::STATUS_UNPAID] > 0 ? 'panel-danger' : 'panel-success';
                $sidebar["My Invoices Summary"]->setAttribute("class", $panelClass);
            } else {
                $sidebar["My Invoices Summary"]->setFooterHtml("");
            }
        } else {
            $params["primarySidebar"]->removeChild("My Invoices Summary");
        }

        $params['clientsstats']['numunpaidinvoices'] = $counters[Invoices::STATUS_UNPAID];

        //My Invoices Status Filter
        if (!empty($sidebar["My Invoices Status Filter"])) {
            $sidebarItems = $sidebar["My Invoices Status Filter"]->getChildren();
            foreach ($counters as $status => $count) {
                if (isset($sidebarItems[$status])) {
                    $sidebarItems[$status]->setBadge($count);
                }
            }
        }

        //Panels in Home page - overdue invoices
        if (!empty($params["panels"]["Overdue Invoices"]) && $overDueCounter > 0) {
            $body = Whmcs::lang("clientHomePanels.overdueInvoicesMsg", ["numberOfInvoices" => $overDueCounter, "balanceDue" => formatCurrency($overDueBalance)]);
            $params["panels"]["Overdue Invoices"]->setBodyHtml("<p>{$body}</p>");
        } else {
            unset($params["panels"]["Overdue Invoices"]);
        }

        //Panels in Home page - unpaid invoices
        if (!empty($params["panels"]["Unpaid Invoices"]) && $counters[Invoices::STATUS_UNPAID] > 0) {
            $body = Whmcs::lang("clientHomePanels.unpaidInvoicesMsg", ["numberOfInvoices" => $counters[Invoices::STATUS_UNPAID], "balanceDue" => formatCurrency($balance)]);
            $params["panels"]["Unpaid Invoices"]->setBodyHtml("<p>{$body}</p>");
        } else {
            unset($params["panels"]["Unpaid Invoices"]);
        }

        if (basename(Server::get("SCRIPT_NAME")) == 'viewinvoice.php' && !empty($params["invoiceid"]) && $reseller->exists) {
            if (!$reseller->settings->admin->resellerInvoice &&
                $reseller->settings->admin->branding &&
                $reseller->settings->admin->cname &&
                $reseller->settings->private->domain) {
                global $CONFIG;
                $params["paymentbutton"] = str_replace(rtrim($CONFIG["SystemURL"], '/'), rtrim(Server::getSystemURL($reseller->settings->private->domain), '/'), $params["paymentbutton"]);
            }
        }

        if ($reseller->settings->admin->resellerInvoice || ($params['clientsstats']['numunpaidinvoices'] && $params['clientsstats']['numunpaidinvoices'] < 2)) {
            $primaryNavbar  = \Menu::primaryNavbar();
            unset($primaryNavbar["Billing"]["Mass Payment"]);
            unset($params["secondarySidebar"]["Billing"]["Mass Payment"]);
        }
        return $params;
    }

    public function setBrandingForSupport($params)
    {
        $reseller = ResellerHelper::getByCurrentURL();
        if(!$reseller->exists || !$reseller->settings->admin->cname)
        {
            return $params;
        }

        global $CONFIG;

        //Brand download links
        if($params["downloads"])
        {
            foreach ($params["downloads"] as $key => $download) {
                $parsed = parse_url($CONFIG["SystemURL"]);
                $newUrl = str_replace($parsed["host"], $reseller->settings->private->domain, $download["link"]);
                $params["downloads"][$key]["link"] = $newUrl;
            }
        }

        if($params["mostdownloads"])
        {
            foreach ($params["mostdownloads"] as $key => $download) {
                $parsed = parse_url($CONFIG["SystemURL"]);
                $newUrl = str_replace($parsed["host"], $reseller->settings->private->domain, $download["link"]);
                $params["mostdownloads"][$key]["link"] = $newUrl;
            }
        }

        return $params;
    }

    public function setBrandingForKnowledgebase($params)
    {
        $reseller = ResellerHelper::getCurrent();
        if($reseller->exists && $reseller->settings->admin->disableKb)
        {
            unset($params["kbmostviews"]);
            unset($params["kbcats"]);
            unset($params["kbarticles"]);
            unset($params["kbarticle"]);
            unset($params["primarySidebar"]["Support Knowledgebase Categories"]);
            unset($params["secondarySidebar"]["Support Knowledgebase Tag Cloud"]);
            unset($params["secondarySidebar"]["Support"]["Knowledgebase"]);

            if(in_array($params["templatefile"], ["knowledgebase", "knowledgebasecat"]))
            {
                Redirect::toPageWithQuery("index.php");
            }
        }

        return $params;
    }

    public function setPricingForDomainsRenewal($params)
    {
        $reseller = ResellerHelper::getByCurrentURL();
        if(!$reseller->exists || empty(Session::get("uid")))
        {
            return $params;
        }

        $mainKey = $params["renewals"] ? "renewals": "renewalsData"; //The key depends on the current URL ...

        //Domains Renewal
        $cartDomains = new CartDomains();
        $cartDomains->setReseller($reseller)
                    ->setCurrency(CartHelper::getCurrency());

        $repo = new ResellersServices();
        $domainPricing = new DomainPricing();
        if($params[$mainKey])
        {
            foreach ($params[$mainKey] as $key => $renew) {
                //If id is not specified (i.e. in cart) load domain model from database
                if (!isset($renew["id"])) {
                    $domains = new Domains();
                    $items = $domains->getByName($renew["domain"]);
                    $model = $items->where("status", Domains::STATUS_ACTIVE)->first();

                    $renew["id"] = $model->id;
                }

                //unset if does not related with reseller
                $domain = $repo->getByTypeAndRelId(ResellersServices::TYPE_DOMAIN, $renew["id"]);
                if (empty($domain)) {
                    unset($params[$mainKey][$key]);
                    continue;
                }

                $domainHelper = new DomainHelper(($renew["domain"]));

                $domain = $domainPricing->getByTld($domainHelper->getTLD());
                $cartDomains->addDomain($domain);
            }
        }

        if(Request::get("a") == "view") {
            $params[$mainKey] = $cartDomains->insertCartRenewalsPricing($params[$mainKey]);
        }
        else {
            $params[$mainKey] = $cartDomains->insertRenewalsPricing($params[$mainKey]);
        }

        return $params;
    }

    /**
     * Remove payment gateways that are disabled by admin in Reseller configuration
     *
     * @param type $params
     * @return type
     */
    public function removeDisabledGateways($params)
    {
        $reseller = ResellerHelper::getCurrent();
        if (!$reseller->exists)
        {
            return $params;
        }

        if(!$reseller->settings->admin->disableEndClientInvoices)
        {
            if ($reseller->settings->admin->resellerInvoice)
            {
                if(Request::get("action") == "addfunds")
                {   
                    //Add funds case
                    $invoiceid = Session::getAndClear("ResellerInvoices")[0];
                    if($invoiceid)
                    {
                        Redirect::to(Server::getCurrentSystemURL(), "rcviewinvoice.php", ["id" => $invoiceid]);
                    }  
                }
                
                $params["gateways"] = [];
                $gateways = Helper::getCustomGateways($reseller->id);

                foreach ($gateways as $gateway)
                {
                    if ($gateway->enabled)
                    {
                        $normalisedName = $gateway->getNormalisedName();
                        $params["gateways"][$normalisedName] = array(
                            "sysname" => $normalisedName,
                            "name" => $gateway->displayName,
                            "type" => $gateway->getType(),
                        );
                        $paymentType = $gateway->getPaymentType();

                        if ($paymentType) {
                            $params["gateways"][$normalisedName]["payment_type"] = $paymentType;
                        }
                    }
                }
            }
            else
            {
                $availableGataweys = $reseller->settings->admin->gateways;
                if($params["gateways"])
                {
                    foreach ($params["gateways"] as $key => $gateway) {
                        if (is_array($availableGataweys) && !in_array($gateway["sysname"], $availableGataweys)) {
                            unset($params["gateways"][$key]);
                        }
                    }
                }
            }

            //Select default gateway (first from list)
            $default = is_array($params["gateways"] ) ? reset($params["gateways"]) : [];
            $params["selectedgateway"] = $default["sysname"];
            $params["selectedgatewaytype"] = $default["type"];
        }
        else
        {
            $params["selectedgateway"] = '';
            $params["selectedgatewaytype"] = '';
            $params["gateways"] = array();
        }

        return $params;
    }

    public function setPricesForUpgrade($params)
    {
        $reseller = ResellerHelper::getCurrent();
        if(!$reseller->exists || !isset($params["client"]) || (basename(Server::get("SCRIPT_NAME")) != 'upgrade.php' && Request::get("action") != 'productdetails') )
        {
            return $params;
        }

        //Check if product can be upgraded
        $currency = new Currency($params["client"]->currency);

        //Set products in order view
        $hostings = new Hostings();
        $hosting = $hostings->find($params["id"]);

        $view = new View($reseller, $currency);
        $params["upgradepackages"] = $view->getUpgradePackagesView($hosting->packageid);

        if(empty($params["upgradepackages"]))
        {
            $params["packagesupgrade"] = 0;
            unset($params["primarySidebar"]["Service Details Actions"]["Upgrade/Downgrade"]);
        }

        return $params;
    }

    public function setResellerVarInTemplate($params)
    {
        $reseller = ResellerHelper::getCurrent();
        if($reseller->id)
        {
            //Set reseller variable in smarty
            $params["reseller"] = $reseller->id;
        }

        return $params;
    }

    public function setAfterUpgradeBranding($params)
    {
        //Set error on upgrade page
        $params["upgradenotavailable"] = Session::getAndClear("upgradeResellerError");

        //Check if update has been completed
        if (basename(Server::get("SCRIPT_NAME")) != "upgrade.php" || Request::get("step") != "4") {
            return $params;
        }

        if ($_SESSION["RC_preventRedirectAfterUpgrade"]) {
            Session::clear("RC_preventRedirectAfterUpgrade");
            Session::clear("ResellerInvoices");
            $_SESSION["upgradeorder"]["invoiceid"] = 0;
            Redirect::to(Server::getCurrentSystemURL(), "upgrade.php", ["step" => "4"]);
        }

        $reseller = ResellerHelper::getCurrent();

        if (!$reseller->exists || !$reseller->settings->admin->resellerInvoice) {
            return $params;
        }

        $resellerInvoicesId = Session::get("ResellerInvoices");

        if (empty($resellerInvoicesId)) {
            return $params;
        }

        $invoiceId = $resellerInvoicesId[0];
        $invoice = new ResellersCenterInvoice($invoiceId);

        //Why add credit ?
//        global $CONFIG;
//        if (!$CONFIG["NoAutoApplyCredit"]) {
//            $client = new Client(Session::get("uid"));
//            $order = $client->orders->sortByDesc("id")->first();
//            $client->credits->add($order->amount, "Resellers Center: Refund upgrade invoice payment");
//        }

        $gateway = $invoice->payments->getGateway();

        //Redirect to new invoice
        $target = ($gateway && $gateway->getType() == "CC" && $invoice->status == Invoices::STATUS_UNPAID) ? "rccreditcard.php" : "rcviewinvoice.php";
        $invoiceKey = ($target == "rccreditcard.php") ? "invoiceid" : "id";
        Session::clear("ResellerInvoices");
        Redirect::to(Server::getCurrentSystemURL(), $target, [$invoiceKey => $invoice->id]);

        return $params;
    }

    /**
     * Check if promotions are available in reseller store
     * and reseller validate promo code
     *
     * @param type $params
     * @return type
     */
    public function validateUpgradePromotionCode($params)
    {
        if(basename(Server::get("SCRIPT_NAME")) != "upgrade.php")
        {
            return $params;
        }

        $code   = Request::get("promocode");
        $regex  = str_replace("#", "[0-9]*", ResellerPromotion::PREFIX);

        //Add alerts to promocode validation
        if(Request::get("promoinvalid"))
        {
            $params["promoerror"] = Whmcs::lang("ordercodenotfound");
        }
        elseif(Request::get("promovalid"))
        {
            $params["promocode"]  = preg_replace("/$regex/", "", $code);
        }

        //Run this hook only on promo validation
        if(!(Request::get("promoinvalid") || Request::get("promovalid")) && !empty($code))
        {
            //Check if we are in reseller store
            $reseller   = ResellerHelper::getCurrent();
            if($reseller->exists)
            {
                //Provided promocodes connot has reseller prefix
                if(preg_match("/$regex/", $code))
                {
                    //Refresh to remove changes in order summary
                    unset($_SESSION["cart"]["promo"]);
                    unset($_REQUEST["promocode"]);
                    Redirect::query(array_merge(["promoinvalid" => 1], $_REQUEST));
                }

                //We are in Reseller shop and some is validating code (without prefix)
                $promocode = new ResellerPromotion(null, $code, $reseller);
                if($promocode->exists)
                {
                    $_REQUEST["promocode"] = $_SESSION["cart"]["promo"] = $promocode->getPrefix().$promocode->code;
                    $_REQUEST["promovalid"] = 1;
                    Redirect::query($_REQUEST);
                }

                unset($_SESSION["cart"]["promo"]);
                unset($_REQUEST["promocode"]);
                Redirect::query(array_merge(["promoinvalid" => 1], $_REQUEST));
            }
        }

        return $params;
    }

    public function manageDeferredGateway($params)
    {
        $creditLineService = new CreditLineService();
        $client = CartHelper::getCurrentClient();

        $creditLineEnable = (bool)$creditLineService->getEnableCreditLine($client->id)->limit;
        $consolidatedEnable = SettingsManager::isConsolidatedEnableForCurrentReseller(null);

        //Remove Deferred Payments gateway if no Deferred system is enabled
        if (!$creditLineEnable && !$consolidatedEnable) {
            $params['gateways'] = array_filter(($params['gateways'] ?: []), function ($value) {
                return strtolower($value['sysname']) != DeferredPayments::SYS_NAME;
            });
        }

        //Remove all gateways except Deferred Payments gateway
        if ($consolidatedEnable) {
            $params['gateways'] = array_filter(($params['gateways'] ?: []), function ($value) {
                return strtolower($value['sysname']) == DeferredPayments::SYS_NAME;
            });
            $deferredGatewayKey = array_key_first(($params['gateways'] ?: []));
            $params["selectedgateway"] = $params['gateways'][$deferredGatewayKey]['sysname'];
            $params["canUseCreditOnCheckout"] = false;
            $params["forceRemoveCreditPayment"] = true;
            $params["applyCredit"] = false;
        }

        return $params;
    }

    /**
     * Get all domains from resellers configuration
     * and set them to /domain/pricing page
     *
     * @param type $params
     * @return type
     */
    private function setDomainPricingView($params)
    {
        $reseller = Reseller::getCurrent();

        if (basename(Server::get('SCRIPT_NAME')) !== "index.php" || !$reseller->exists || !Whmcs::isVersion('8.0')) {
            return $params;
        }

        $currency   = CartHelper::getCurrency();
        $view       = new View($reseller, $currency);
        $domains = $view->getDomainsView(null);

        /* Overwriting WHMCS domain list with pricing and categories */
        $params['pricing'] = $domains['pricing']['pricing'];
        $params['tldCategories'] = $domains['categoriesWithCounts'];
        $params['featuredTlds'] = $domains['spotlightTlds'];

        return $params;
    }

    /**
     * Get all resellers product groups and override those WHMCS default ones
     * in Twenty-One's index.php template
     *
     * @param type $params
     * @return type
     */
    private function overrideProductGroupsView($params)
    {
        $reseller = Reseller::getCurrent();
        $scriptName = basename(Server::get('SCRIPT_NAME'));

        if (($scriptName !== "index.php" && $scriptName !== "clientarea.php") ||  !$reseller->exists) {
            return $params;
        }

        $currency   = CartHelper::getCurrency();
        $view       = new View($reseller, $currency);

        /* Override 'productGroups' parameter by collection of WHMCS product groups which contains only those branded */
        $params["productGroups"] = $view->getBrandedWhmcsProductGroups($currency);
        $params["products"] = $view->getProductsView($params["gid"]);

        $view->setSecondarySidebar($params, $currency);

        return $params;
    }

    private function blockPages( $params )
    {
        $reseller = Reseller::getByCurrentURL();
        if( !$reseller->exists ) {
            return;
        }

//        if ($this->containCurrentUrlDomainPricing()) {
//            global $CONFIG;
//            $domain = $CONFIG['SystemURL'];
//            Redirect::to($domain, 'index.php');
//        }
    }

    private function checkIsAddonsPage( $params )
    {
        if (!empty($params['gid']) && !is_numeric($params['gid']) ) {
            if (Reseller::isMakingOrderForClient()) {
                Redirect::to(Server::getCurrentSystemURL(), "index.php");
            }
        }
    }

    private function redirectToNotEmptyGroupIfNecessary( $params )
    {
        if( !$params['inShoppingCart'] )
        {
            return;
        }

//        Addons
        if( empty($params['gid']) || !is_numeric($params['gid']) )
        {
            return;
        }

        if( Reseller::isMakingOrderForClient() )
        {
            $reseller = Reseller::getCurrent();
        }
        else
        {
            $reseller = Reseller::getByCurrentURL();
        }

        if( !$reseller->exists )
        {
            return;
        }

        $currency = CartHelper::getCurrency();
        $showHidden = ((new ResellersSettings())->getSetting('showHidden', ResellersSettings::RESELLERS_DEFAULT_CONFIGURATION_ID) === 'on');

        $gids = [];

        foreach( $reseller->contents->products as $product )
        {
            $pricing = $product->getPricing($currency);
            $branded = $pricing->getBranded();

            if( !empty($branded) && ($showHidden || !$product->group->hidden) )
            {
                $gids[] = $product->gid;
            }
        }

        $gids = array_unique($gids);

        if( empty($gids) )
        {
            return;
        }

        if( in_array($params['gid'], $gids) )
        {
            return;
        }

        if(WHMCS::isVersion('8.0'))
        {
            $slug = '';
            foreach($params['productgroups'] as $productGroup)
            {
                if($productGroup['gid'] == $gids[0])
                {
                    $slug = $productGroup['slug'];
                    break;
                }
            }

            Redirect::to(Server::getCurrentSystemURL(), "index.php", ["rp" => '/store/' . ($slug?:$this->getProductGroupSlug($gids[0]))]);
        }
        else
        {
            Redirect::to(Server::getCurrentSystemURL(), "cart.php", ["gid" => $gids[0]]);
        }
    }

    private function getProductGroupSlug($gid)
    {
        $prodGroup = \MGModule\ResellersCenter\models\whmcs\ProductGroup::find($gid);
        return $prodGroup->slug;
    }

    private function setInvoicesDateFormat(&$params)
    {
        if (!$params['invoices'] || !$this->checkIsResellerClient()) {
            return $params;
        }
        $resellersClientsRepo = new ResellersClients();
        $resellerClient = $resellersClientsRepo->getByRelid($params['clientsdetails']['userid']);

        $customDateFormatSetting = (new ResellersSettings())->getSetting('customDateFormat', $resellerClient->reseller_id);

        if (!$customDateFormatSetting || $customDateFormatSetting != 'on') {
            return $params;
        }

        $format = (new ResellersSettings())->getSetting('dateFormat', $resellerClient->reseller_id, true);
        $dateFormatter = new DateFormatter();

        foreach($params['invoices'] as &$invoice) {
            $invoice['datecreated'] = $dateFormatter->format($invoice['normalisedDateCreated'], $format);
            $invoice['datedue'] = $dateFormatter->format($invoice['normalisedDateDue'], $format);
        }

        return $params;
    }

    private function setServicesDateFormat(&$params)
    {
        if (!$params['services'] || !$this->checkIsResellerClient()) {
            return $params;
        }

        $resellersClientsRepo = new ResellersClients();
        $resellerClient = $resellersClientsRepo->getByRelid($params['clientsdetails']['userid']);

        $customDateFormatSetting = (new ResellersSettings())->getSetting('customDateFormat', $resellerClient->reseller_id);

        if (!$customDateFormatSetting || $customDateFormatSetting != 'on') {
            return $params;
        }

        $format = (new ResellersSettings())->getSetting('dateFormat', $resellerClient->reseller_id, true);
        $dateFormatter = new DateFormatter();

        foreach($params['services'] as &$service) {
            if ($service['normalisedRegDate'] != DateFormatter::ZERO_DATE) {
                $service['regdate'] = $dateFormatter->format($service['normalisedRegDate'], $format);
            }

            if ($service['normalisedNextDueDate'] != DateFormatter::ZERO_DATE) {
                $service['nextduedate'] = $dateFormatter->format($service['normalisedNextDueDate'], $format);
            }
        }

        return $params;
    }

    private function checkIsResellerClient():bool
    {
        $isReseller       = ResellerHelper::isReseller(Session::get('uid'));
        $isResellerClient = (new ResellersClients())->getByRelid(Session::get('uid'))->exists;

        if ( $isReseller || (!$isResellerClient && !ResellerHelper::getByCurrentURL()->exists) ) {
            return false;
        }
        return true;
    }

    private function containCurrentUrlDomainPricing(): bool
    {
        $urlCases = [Server::get('SCRIPT_URL'), Server::get('REQUEST_URI'), Server::get('QUERY_STRING')];

        foreach ($urlCases as $urlCase) {
            $params = explode('/', $urlCase);
            if (in_array('domain', $params) && in_array('pricing', $params)) {
                return true;
            }
        }

        return false;
    }
}

//On invoice view
if(basename(Server::get("SCRIPT_NAME")) == 'viewinvoice.php' && !ResellerHelper::isMakingOrderForClient() && !ResellerHelper::isReseller($_SESSION['uid']))
{
    $reseller = ResellerHelper::getCurrent();

    //Add funds case
    $invoiceid = Session::getAndClear("ResellerInvoices")[0];

    if($invoiceid)
    {
        Redirect::to(Server::getCurrentSystemURL(), "rcviewinvoice.php", ["id" => $invoiceid]);
    }


    //Check if we are in resellers shop
    if($reseller->exists)
    {
        $repo = new Invoices();
        $invoice = $repo->find(Request::get("id"));

        //Redirect to custom page if reseller has ResellersCenterInvoice enabled
        if($reseller->settings->admin->resellerInvoice && $invoice->userid != Session::get("uid"))
        {
            $domain = Server::get("HTTP_HOST");
            $path = str_replace(basename(Server::get("SCRIPT_NAME")), "", Server::get("SCRIPT_NAME"));

            Redirect::to($domain, "{$path}rcviewinvoice.php", $_REQUEST);
        }

        //Check if invoice belongs to reseller's logged client
        foreach($invoice->items as $item)
        {
            if($item->service->resellerService)
            {
                $reseller = $item->service->resellerService->reseller;
                $domain = $reseller->settings->private->domain;
                $page   = substr(Server::get("SCRIPT_NAME"), 1);
                $query = $_REQUEST;

                if(empty($domain) || !$reseller->settings->admin->cname)
                {
                    $domain = Server::get("HTTP_HOST");
                    $query = array_merge($_REQUEST, array("resid" => $reseller->id));
                }

                if(!Session::get("branded"))
                {
                    Redirect::to($domain, $page, $query);
                }
            }
        }
    }
}

if (basename(Server::get("SCRIPT_NAME")) == 'supporttickets.php' || basename(Server::get("SCRIPT_NAME")) == 'submitticket.php') {
    $reseller = ResellerHelper::getCurrent();
    if ($reseller->exists) {
        $departments = $reseller->settings->admin->ticketDeptids;
        if (empty($departments)) {
            Redirect::toPageWithQuery("clientarea.php", []);
        }
    }
}

if(ClientAreaHelper::isClientArea() && !empty($GLOBALS["whmcs"]) && !Request::get("systpl"))
{
    $reseller = ResellerHelper::getCurrent();
    if($reseller->exists)
    {
        $reseller->view->overrideConfig();
    }
}

/**
 * This redirect is required for URL generated in emails when reseller does not have CNAME enabled
 */
if(Request::exists('rcredirect'))
{
    $redirect = Request::get('rcredirect');
    Server::getCurrentSystemURL();
    Redirect::toPageWithQuery($redirect);
}

if(ClientAreaHelper::isClientArea() && Request::get("rctoken"))
{
    $reseller = ResellerHelper::getByCurrentURL();
    $token = Request::get("rctoken");
    if(!empty($token) && $reseller->exists)
    {
        Session::restore($token);

        //Refresh to load proper client and remove key parameter
        $domain = Server::get("HTTP_HOST");
        $page = substr(Server::get("SCRIPT_NAME"), 1);
        $query = $_REQUEST;

        unset($query["rctoken"]);
        Redirect::to($domain, $page, $query);
    }
}

/**
 * WHMCS V8
 *
 * prevent displaying errors coming from EmailPreSend - abortsend
 */

if(WHMCS::isVersion('8.0') && Session::getAndClear('sentByResellersCenter'))
{
    $flash = new \WHMCS\FlashMessages();
    $flash->get();
    $flash->add( 'Email sent successfully!', 'success');
}
