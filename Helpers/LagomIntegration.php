<?php

namespace MGModule\ResellersCenter\Helpers;

use MGModule\ResellersCenter\Core\Resources\Resellers\Reseller;
use \WHMCS\Database\Capsule as DB;

class LagomIntegration
{
    const PRODUCTS_GROUPS_MENU_ITEM = "Product Groups";
    const WHMCS_MENU_ITEM_KEY = 'whmcs';
    const CUSTOM_MENU_ITEM_KEY = 'custom';
    const DEFAULT_LANGUAGE = 'english';

    public static function hasResellerLagomTemplate(Reseller $reseller): bool
    {
        $lagomKeys = [
            'lagom',
            'lagom2'
        ];
        return in_array($reseller->settings->private->whmcsTemplate, $lagomKeys);
    }

    public static function getProductGroupsMenuItems():array
    {
        try {
            $menuItemsTable = 'rsthemes_menus_items';
            $parentItemsIds = DB::table($menuItemsTable)->select('parent_id')->where('title' , self::PRODUCTS_GROUPS_MENU_ITEM)->get()->pluck('parent_id');
            $results = DB::table($menuItemsTable)->select('title')->whereIn('content_menu_id', $parentItemsIds)->get();

            $menuItemNames = [];

            foreach ($results as $result) {
                $resultElements = (array)json_decode($result->title);
                $whmcsName = $resultElements[self::WHMCS_MENU_ITEM_KEY];
                $customNames = (array)$resultElements[self::CUSTOM_MENU_ITEM_KEY] ?: [];

                $itemName = $whmcsName ?: $customNames[self::DEFAULT_LANGUAGE];

                if (empty($itemName)) {
                    $availableLanguages = array_filter($customNames);
                    $itemName = array_values($availableLanguages)[0];
                }

                if ($itemName) {
                    $menuItemNames[] = $itemName;
                }
            }

            return $menuItemNames;

        } catch (\Exception $e) {
            return [];
        }
    }
}