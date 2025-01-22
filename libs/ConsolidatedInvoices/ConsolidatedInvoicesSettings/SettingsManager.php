<?php

namespace MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings;

use MGModule\ResellersCenter\Core\Helpers\CartHelper;
use MGModule\ResellersCenter\Core\Helpers\Reseller as ResellerHelper;
use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Interfaces\SettingInterface;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings\EnableConsolidatedInvoices;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings\EndClientConsolidatedInvoices;
use MGModule\ResellersCenter\repository\ResellersClients;
use MGModule\ResellersCenter\repository\ResellersClientsSettings;
use ReflectionClass;

class SettingsManager
{
    public static function getAdminAreaSettings():array
    {
        return array_filter(self::getSettings(), function ($setting) {
            return $setting->forAdminArea();
        });
    }

    public static function getResellerClientSettings():array
    {
        return array_filter(self::getSettings(), function ($setting) {
            return $setting->forResellerClient();
        });
    }

    protected static function getSettings():array
    {
        $settingList = [];
        $settingsDir = __DIR__ . '/Settings';
        $settings = scandir($settingsDir);
        $settings = array_diff($settings, ['.', '..']);

        $reflectionClass = new ReflectionClass(self::class);

        $settingNamespace = $reflectionClass->getNamespaceName() . "\Settings\\";

        foreach ($settings as $setting) {
            $className = explode('.', $setting)[0];
            $classFullName = $settingNamespace . $className;
            if (!class_exists($classFullName)) {
                continue;
            }
            $settingObject = new $classFullName();
            if (!$settingObject instanceof SettingInterface) {
                continue;
            }
            $settingList[] = $settingObject;
        }
        return $settingList;
    }

    public static function getSetting($userId, $settingName)
    {
        if (ResellerHelper::isReseller($userId)) {
            $reseller = new Reseller(null, $userId);
            return self::getSettingFromReseller($reseller, $settingName);
        } else {
            $resellerClient = (new ResellersClients())->getByRelid($userId);
            return self::getSettingFromResellerClient($resellerClient,  $settingName);
        }
    }

    public static function getSettingFromReseller($reseller,  $settingName)
    {
        return $reseller->settings->admin->$settingName ?: self::getSettingFromPricingGroup($reseller->group, $settingName);
    }

    public static function getSettingFromResellerClient($resellerClient,  $settingName)
    {
        if ($resellerClient->exists) {
            $resellerClientsSettings = new ResellersClientsSettings();
            return $resellerClientsSettings->getSetting($resellerClient, $settingName);
        }

        return null;
    }

    public static function getSettingFromPricingGroup($group,  $settingName)
    {
        $settingsModel = $group->settings;
        return $settingsModel->where('setting', $settingName)->first()->value;
    }

    public static function isConsolidatedEnableForCurrentReseller($reseller):bool
    {
        if (!$reseller->exists) {
            $reseller = ResellerHelper::getCurrent();
        }

        if ($reseller->exists) {
            $client = CartHelper::getCurrentClient();
            $consolidatedEnable = SettingsManager::getSettingFromResellerClient($client->resellerClient, EnableConsolidatedInvoices::NAME) == 'on' &&
                SettingsManager::getSettingFromReseller($reseller, EndClientConsolidatedInvoices::NAME) == 'on';
        } else {
            $reseller = \MGModule\ResellersCenter\Core\Helpers\Reseller::getLogged();
            $consolidatedEnable = $reseller->exists &&
                $reseller->settings->admin->resellerInvoice &&
                SettingsManager::getSettingFromReseller($reseller, EnableConsolidatedInvoices::NAME) == 'on';
        }

        return $consolidatedEnable;
    }

}