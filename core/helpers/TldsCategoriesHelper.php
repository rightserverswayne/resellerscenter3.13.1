<?php

namespace MGModule\ResellersCenter\Core\Helpers;

class TldsCategoriesHelper
{
    public static function getCategories( $tld = null )
    {
        $tldsFile = implode(DIRECTORY_SEPARATOR, [ROOTDIR, 'resources', 'domains', 'dist.categories.json']);
        if( !file_exists($tldsFile) )
        {
            return [];
        }
        $categories = json_decode(file_get_contents($tldsFile), true);
        if( !$tld )
        {
            return array_keys($categories);
        }

        $out = [];

        foreach( $categories as $tldCategory => $tldCategoryItems )
        {
            if( in_array($tld, $tldCategoryItems, true) )
            {
                $out[$tldCategory]++;
            }
        }
        return $out;
    }
}