<?php

/* * ********************************************************************
 * MGMF product developed. (2016-02-23)
 * *
 *
 *  CREATED BY MODULESGARDEN       ->       http://modulesgarden.com
 *  CONTACT                        ->       contact@modulesgarden.com
 *
 *
 * This software is furnished under a license and may be used and copied
 * only  in  accordance  with  the  terms  of such  license and with the
 * inclusion of the above copyright notice.  This software  or any other
 * copies thereof may not be provided or otherwise made available to any
 * other person.  No title to and  ownership of the  software is  hereby
 * transferred.
 *
 *
 * ******************************************************************** */

namespace MGModule\ResellersCenter\controllers\addon\clientarea;
use MGModule\ResellersCenter\core\MailConfiguration;
use MGModule\ResellersCenter\core\Mailer;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings\EndClientConsolidatedInvoices;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\mgLibs\Helpers\DateFormatter;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;

use MGModule\ResellersCenter\models\whmcs\ConfigOption;
use MGModule\ResellersCenter\models\whmcs\EmailTemplate;
use MGModule\ResellersCenter\repository\PaymentGateways;
use MGModule\ResellersCenter\repository\EmailTemplates as RCEmailTemplates;
use MGModule\ResellersCenter\repository\whmcs\EmailTemplates as WHMCSEmailTemplates;
use MGModule\ResellersCenter\repository\ResellersSettings;
use \MGModule\ResellersCenter\models\whmcs\Configuration as WHMCSConfig;

use MGModule\ResellersCenter\core\helpers\Helper;
use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\core\helpers\ClientAreaHelper as CAHelper;

use MGModule\ResellersCenter\core\MergeFields;
use MGModule\ResellersCenter\core\EventManager;
use MGModule\ResellersCenter\core\FileUploader;
use MGModule\ResellersCenter\mgLibs\Lang;

use MGModule\ResellersCenter\core\Server;
use MGModule\ResellersCenter\core\Request;

/**
 * Description of Product
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Configuration extends AbstractController
{
    public function indexHTML()
    {
        $vars = [];
        $reseller = Reseller::getLogged();

        //Get global settings for reseller
        $vars["globalSettings"] = $reseller->settings->admin;

        $settings = $reseller->settings->private;
        $settings->dateFormat = $settings->dateFormat ?? DateFormatter::getGlobalFormat();
        $vars["settings"] = $settings;

        //Get whmcs email templates
        $emails = new WHMCSEmailTemplates();
        $vars["emailTemplates"] = $emails->getTemplatesSortedByType();
        foreach ($vars["emailTemplates"] as $type => $templates) {
            foreach ($templates as $key => $template) {
                if (!$reseller->settings->admin->emailTemplates || !array_key_exists($template->name, $reseller->settings->admin->emailTemplates)) {
                    unset($vars["emailTemplates"][$type][$key]);
                }
            }
        }

        //Reseller url to branded WHMCS - needed when CNAME is disabled
        $vars["resellerUrl"] = Server::getCurrentURL(["resid" => $reseller->id]);

        //Load gateways only if reseller invoice is selected
        if ($vars["globalSettings"]->resellerInvoice) {
            $vars["gateways"] = Helper::getCustomGateways($reseller->id);

            //Check if there is at least one gateway enabled
            $vars["noGatewayEnabled"] = true;
            foreach ($vars["gateways"] as $gateway) {
                if ($gateway->enabled) {
                    $vars["noGatewayEnabled"] = false;
                    break;
                }
            }
        }

        $dataExportTypes = ['Domains','Clients','Invoices','Addons','Hosting','Transactions'];

        $vars['dataExportTypes']   = $dataExportTypes;
        $vars["availableSecureTypes"] = MailConfiguration::SECURE_TYPES;
        $vars["availableEmailTemplates"] = $vars["globalSettings"]->emailTemplates ?: [];
        $vars["dateFormats"] = DateFormatter::getDateFormats();

        $vars["reminders"] = json_decode($vars["settings"]->reminders);

        $vars["remindersTemplates"] = $this->getInvoiceEmailTemplates();
        $vars["endClientConsolidatedInvoices"] = SettingsManager::getSettingFromReseller($reseller, EndClientConsolidatedInvoices::NAME);

        return ['tpl'  => 'base','vars' => $vars];
    }

    public function saveJSON()
    {
        try {
            $data = Request::get("settings");

            $reminders = Request::get("reminders");
            $data['reminders'] = json_encode($this->parseReminders($reminders));

            $data = $this->formatSaveData($data);
            $reseller = Reseller::getLogged();

            $this->validateSettings($data);

            //Save
            $reseller->settings->private->save($data);

            //Save Payment Gateways
            $gateways = Request::get("gateways");
            $repo = new PaymentGateways();

            if ( is_array($gateways) || is_object($gateways) ) {
                foreach ( $gateways as $gateway => $settings ) {
                    $repo->saveGateway($reseller->id, $gateway, $settings);
                }
            }

            EventManager::call("configurationPrivateSaved", $reseller->id);
            return array("success" => Lang::T('form','success'));
        } catch(\Exception $ex) {
            return array("error" => Lang::absoluteT('exceptions', $ex->getMessage()));
        }
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
     * GENERAL
     */

    public function getConfigJSON()
    {
        $apiPath = 'modules/addons/ResellersCenter/api/resellerApi.php?report=[TYPE]&email=xxx&token=[API KEY]';
        $url = WHMCSConfig::where('setting', 'SystemURL')->first();
        echo $url->value . $apiPath;
        exit;
    }

    public function uploadLogoJSON()
    {
        $reseller = Reseller::getLogged();
        $type = Request::get("type");

        $logoDir = ADDON_DIR . "/storage/logo/";
        $filename = $reseller->id."RC".$type.'.'.pathinfo(basename($_FILES[$type]["name"]), PATHINFO_EXTENSION);
        $file = new FileUploader($type, $filename, $logoDir);

        if ($file->isImage()) {
            $result = $file->upload();

            if ($result == "success") {
                EventManager::call("logoUploaded", $filename);
                return [
                    "logo" => $filename,
                    "htmllogopath" => "modules/addons/ResellersCenter/storage/logo/".$filename
                ];
            } else {
                return array("error" => $result);
            }
        }

        return ["error" => Lang::T('form','imageError')];
    }

    public function deleteLogoJSON()
    {
        $reseller = Reseller::getLogged();
        $type = Request::get("type");

        //remove logo
        $logoDir = ADDON_DIR . "/storage/logo/";
        $logoPath = $logoDir . $reseller->settings->private->$type;
        unlink($logoPath);

        $rs = new ResellersSettings();
        $rs->saveSingleSetting($reseller->id, $type, "", true);

        return ["success" => Lang::T('form','logoDeleted')];
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
     * EMAIL TEMPLATES
     */

    public function editTemplateHTML()
    {
        $reseller = Reseller::getLogged();
        $whmcsTemplateName = Request::get("name");

        //Get WHMCS template default lang template
        $whmcsTemplates = new WHMCSEmailTemplates();
        $available = $whmcsTemplates->getByName($whmcsTemplateName, "");

        $repo = new RCEmailTemplates();
        $templates = $repo->getByName($reseller->id, $available->name);

        if ($templates->isEmpty()) {
            $templates[] = $available;
        }

        $fields = new MergeFields();
        return array(
           'tpl'  => 'emailTemplates/details',
           'vars' => array(
               "templates" => $templates,
               "templateType" => $available->type,
               "mergeFields" => $fields->fields,
               "languages" => Whmcs::getAllLanguages()
            )
        );
    }

    public function getAvailableLanguagesJSON()
    {
        $reseller = Reseller::getLogged();
        $languages = Whmcs::getAllLanguages();

        $repo = new RCEmailTemplates();
        $templates = $repo->getByName($reseller->id, Request::get("name"));

        foreach($templates as $template)
        {
            $key = array_search(ucfirst($template->language), $languages);
            if($key !== false)
            {
                unset($languages[$key]);
            }
        }

        return $languages;
    }
    
    public function saveTemplateJSON()
    {
        $reseller = Reseller::getLogged();
        $name = Request::get("name");
        $templates = Request::get("templates");
        $language = Request::get("selectedLanguage");

        $repo = new RCEmailTemplates();
        $clonedTemplate = $repo->saveTemplates($reseller->id, $name, $templates, $language);

        return [
            "success" => Lang::T('form','success'),
            "clonedTemplate" => $clonedTemplate
        ];
    }
    
    public function deleteTemplateJSON()
    {
        $reseller = Reseller::getLogged();
        $name = Request::get("template");
        $language = Request::get("language");
        
        $repo = new RCEmailTemplates();
        $template = $repo->getByName($reseller->id, $name, $language);
        $repo->delete($template->id);

        return array("success" => Lang::T('form','success'));
    }
    
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
     * PAYMENT GATEWAYS
     */
    
    public function sortPaymentGatewaysJSON()
    {
        $order = Request::get("order");
        $reseller = Reseller::getLogged();
        
        //Save Payment Gateways
        $repo = new PaymentGateways();
        foreach($order as $order => $gateway)
        {
            $repo->updateSingleParam($reseller->id, $gateway, "order", $order);
        }
        
        return array("success" => Lang::T('payments','reorder','success'));
    }

    public function testConnectionJSON()
    {
        $configSettings = Request::get("settings");

        $config = new MailConfiguration();
        $config->setHostname($configSettings['mailHostName']);
        $config->setUsername($configSettings['mailUserName']);
        $config->setPassword($configSettings['mailPassword']);
        $config->setPort($configSettings['mailPort']);
        $config->setSecure($configSettings['smtpSslType']);

        $errorMessage = '';

        try {
            $result = Mailer::checkConnection($config);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $result = false;
        }

        return ['validate'=> $result, 'message'=>$errorMessage];
    }

    public function testMailJSON()
    {
        $configSettings = Request::get("settings");

        $config = new MailConfiguration();
        $config->setHostname($configSettings['mailHostName']);
        $config->setUsername($configSettings['mailUserName']);
        $config->setPassword($configSettings['mailPassword']);
        $config->setPort($configSettings['mailPort']);
        $config->setSecure($configSettings['smtpSslType']);

        $errorMessage = '';

        try {
            $result = Mailer::sendTestEmail($config);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $result = false;
        }

        return ['validate'=> $result, 'message'=>$errorMessage];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function formatSaveData( $data )
    {
        foreach(['tos','domain'] as $value)
        {
            $data[$value] = htmlentities(strip_tags(html_entity_decode($data[$value])));
        }
        if( filter_var($data['domain'], FILTER_VALIDATE_DOMAIN) )
        {
            $data['domain'] = rtrim(trim($data['domain']), '/') . '/';
        }
        return $data;
    }

    protected function getInvoiceEmailTemplates():array
    {
        $templates = [];
        $results = EmailTemplate::select('id', 'name')->where('type', 'invoice')->get();
        foreach ($results as $result) {
            $templates[$result->name] = $result->name;
        }
        return $templates;
    }

    protected function validateSettings($settings)
    {
        if ($settings['gracePeriod'] == null || $settings['gracePeriod'] < 0) {
            throw new \Exception('gracePeriodCannotBeNegativeValue');
        }

        if ($settings['holdPeriod'] == null || $settings['holdPeriod'] < 0) {
            throw new \Exception('holdPeriodCannotBeNegativeValue');
        }

        if ($settings['terminatePeriod'] == null || $settings['terminatePeriod'] < 0) {
            throw new \Exception('terminatePeriodCannotBeNegativeValue');
        }
    }

    protected function parseReminders($reminders)
    {
        $remindersList = [];
        $remindersNames = $reminders['name'] ?: [];
        foreach ($remindersNames as $key=>$value) {
            $remindersList[$key] = [
                'name'=>$value,
                'days'=>$reminders['days'][$key],
                'enable'=>'on'
                ];
        }
        return $remindersList;
    }
}
