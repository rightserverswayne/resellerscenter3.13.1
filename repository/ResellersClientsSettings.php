<?php

namespace MGModule\ResellersCenter\repository;

use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\models\ResellerClient;
use MGModule\ResellersCenter\models\ResellersClientsSetting;
use MGModule\ResellersCenter\Repository\Source\AbstractRepository;

class ResellersClientsSettings extends AbstractRepository
{

    function determinateModel()
    {
        return ResellersClientsSetting::class;
    }

    public function updateSettings($resClientId, $settings)
    {
        $settings = $settings ?: [];
        $model = $this->getModel();

        foreach (SettingsManager::getResellerClientSettings() as $setting) {
            if (in_array($setting->getName(), array_keys($settings))) {
                $setting->validate($settings[$setting->getName()]);
                $settingValue = $settings[$setting->getName()];
            } else {
                $settingValue = $setting->getDefaultValue();
            }
            $model->updateOrCreate(['reseller_client_id' => $resClientId, 'setting' => $setting->getName()],['value'=>$settingValue]);
        }
    }

    public function updateSettingsByClientId($clientId, $settings)
    {
        $resClientsRepo = new ResellersClients();
        $resellersClient = $resClientsRepo->getByRelid($clientId);
        if ($resellersClient->exists) {
            $this->updateSettings($resellersClient->id, $settings);
        }
    }

    public function addDefaultSettingToResellerClient($resellerClient)
    {
        foreach (SettingsManager::getResellerClientSettings() as $setting) {
            $resellerSettings = $resellerClient->reseller->settings;
            $settingValue = $resellerSettings['private'][$setting->getName()];
            if ($settingValue) {
                $defaultSettings[$setting->getName()] = $settingValue;
            }
        }

        $this->updateSettings($resellerClient->id, $defaultSettings);
    }

    public function getClientSettings($resellerClient):array
    {
        $settings = [];

        foreach ($resellerClient->settings as $setting) {
            $settings[$setting->setting] = $setting->value;
        }

        return $settings;
    }

    public function getRequiredSettings()
    {
        return SettingsManager::getResellerClientSettings();
    }

    public function getSetting(ResellerClient $resellerClient, $setting)
    {
        $clientSettings = $this->getClientSettings($resellerClient);
        return array_key_exists($setting, $clientSettings) ?
            $clientSettings[$setting] :
            $resellerClient->reseller->settings['private'][$setting];
    }

}