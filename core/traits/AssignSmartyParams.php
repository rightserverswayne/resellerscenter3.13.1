<?php

namespace MGModule\ResellersCenter\Core\Traits;

trait AssignSmartyParams
{

    public function assignParams($params)
    {
        global $smarty;

        foreach($params as $key => $value)
        {
            $smarty->assign($key, $value);
        }
    }

}