<?php

namespace MGModule\ResellersCenter\Controllers\Addon\Admin;

use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Exceptions\ConsolidatedSettingException;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;

use MGModule\ResellersCenter\repository\CreditLines;
use MGModule\ResellersCenter\repository\Documentations;
use MGModule\ResellersCenter\repository\ResellersSettings;
use MGModule\ResellersCenter\repository\whmcs\TicketDepartments;
use MGModule\ResellersCenter\repository\whmcs\EmailTemplates;
use MGModule\ResellersCenter\repository\whmcs\PaymentGateways;

use MGModule\ResellersCenter\core\helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\mgLibs\Lang;

use MGModule\ResellersCenter\core\Request;

use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;

/**
 * Description of Configuration
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Configuration extends AbstractController
{
    public function indexHTML()
    {
        $vars = array();
        $settings = new ResellersSettings();
        $vars["settings"] = $settings->getSettings(ResellersSettings::RESELLERS_DEFAULT_CONFIGURATION_ID);

        $ticketDepartments = new TicketDepartments();
        $vars["ticketDepts"] = $ticketDepartments->all();
        $emailTemplates = new EmailTemplates();
        $vars["emailTemplates"] = $emailTemplates->getTemplatesSortedByType();
        
        $gateways = new PaymentGateways();
        $vars["gateways"] = $gateways->getEnabledGatewaysArray();
        
        $vars["whmcsTemplates"] = Whmcs::getAvailableTemplates();
        $vars["orderTemplates"] = Whmcs::getAvailableOrderTemplates();
        $vars["invoiceTemplates"] = Helper::getAvailableInvoiceTemplates();
        
        $docsRepo = new Documentations();
        $vars["documentations"] = $docsRepo->all();

        $vars["isWhmcs8"] = Whmcs::isVersion('8.0');

        $vars['whmcsApiKeys'] = WhmcsAPI::getSettings();

        return array(
            'tpl'   => 'base',
            'vars' => $vars
        );
    }
    
    public function saveConfigurationJSON()
    {
        $settings = Request::get("settings");
        $resellerid = Request::get("resellerid") ?: ResellersSettings::RESELLERS_DEFAULT_CONFIGURATION_ID;
        $creditLineSettings = Request::get("creditLineSettings");

        try {
            $this->validateConsolidatedInvoicesSettings($settings);

            //Set -slash- back to original state
            if ($settings["emailTemplates"]) {
                foreach ($settings["emailTemplates"] as $template => $value) {
                    if (strpos($template, "-slash-")) {
                        //remove old name
                        unset($settings["emailTemplates"][$template]);

                        //set correct name
                        $template = str_replace("-slash-", "/", $template);
                        $settings["emailTemplates"][$template] = "on";
                    }
                }
            }

            $reseller = new Reseller($resellerid);
            //Do not allow to change reseller type if reseller has any invoices assinged
            if ($resellerid && ResellerHelper::hasResellerRelatedInvoices($reseller)) {
                $settings["resellerInvoice"] = $reseller->settings->admin->resellerInvoice;
                $settings["invoiceBranding"] = $reseller->settings->admin->resellerInvoice ? "on" : $settings["invoiceBranding"];
            }

            if ($creditLineSettings["creditlinelimit"] !== null) {
                $creditLineRepo = new CreditLines();
                $data['client_id'] = $reseller->client_id;
                $data['limit'] = $creditLineSettings["creditlinelimit"];
                $creditLineRepo->updateOrCreate($data);
                unset($settings["creditlinelimit"]);
            }

            $repo = new ResellersSettings();
            $repo->saveSettings($resellerid, $settings);

            $whmcsApiKeys = Request::get("whmcsApiKeys");
            if ($whmcsApiKeys) {
                WhmcsAPI::saveSettings($whmcsApiKeys);
            }
            EventManager::call("configurationSaved", $resellerid);
            return array("success" => Lang::T('settings','save','success'));
        } catch (ConsolidatedSettingException $e) {
            return array("error" => Lang::absoluteT('consolidatedInvoices','errorMessages', $e->getMessage()));
        } catch (\Exception $e) {
            return array("error" => Lang::T('settings','save','failed'));
        }
    }

    protected function validateConsolidatedInvoicesSettings($settings)
    {
        $consolidatedInvoiceSettings = SettingsManager::getAdminAreaSettings();
        foreach ($consolidatedInvoiceSettings as $setting) {
            if (!isset($settings[$setting->getName()])) {
                continue;
            }
            $setting->validate($settings[$setting->getName()]);
        }
    }
}
