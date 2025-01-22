<?php

namespace MGModule\ResellersCenter\repository\whmcs;

use MGModule\ResellersCenter\Repository\Source\AbstractRepository;

class DynamicTranslations extends AbstractRepository
{
    const PRODUCT_TYPE = "product";
    const PRODUCT_ADDON_TYPE = "product_addon";

    function determinateModel()
    {
        return 'MGModule\ResellersCenter\models\whmcs\DynamicTranslation';
    }

    public function getProductsTranslations($language):array
    {
        return $this->getTranslationsByType($language,self::PRODUCT_TYPE );
    }

    public function getProductAddonsTranslations($language):array
    {
        return $this->getTranslationsByType($language,self::PRODUCT_ADDON_TYPE );
    }

    protected function getTranslationsByType($language, $type):array
    {
        $translations = [];
        $model = $this->getModel();
        $results = $model->where('language', $language)->where('related_type', 'LIKE', $type. '.{id}%')->get();
        foreach ($results as $result) {
            $type = explode('.', $result->related_type);
            $translations[$result->related_id][end($type)] = $result->translation;
        }
        return $translations;
    }
}