<?php

namespace MGModule\ResellersCenter\repository;

use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Exceptions\PricingGroupTurnOnConsolidatedBlocked;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\models\GroupSetting;
use MGModule\ResellersCenter\Repository\Source\AbstractRepository;

class GroupsSettings extends AbstractRepository
{
    function determinateModel()
    {
        return GroupSetting::class;
    }

    public function updateSettings($groupId, array $settings)
    {
        $data = ['pricingGroup' =>$groupId];
        $model = $this->getModel();

        foreach (SettingsManager::getAdminAreaSettings() as $setting) {
            if (in_array($setting->getName(), array_keys($settings))) {
                $setting->validate($settings[$setting->getName()], $data);
                $settingValue = $settings[$setting->getName()];
            } else {
                $settingValue = $setting->getDefaultValue();
            }
            $model->updateOrCreate(['group_id' => $groupId, 'setting' => $setting->getName()],['value'=>$settingValue]);
        }
    }

}