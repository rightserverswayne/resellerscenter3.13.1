<?php

namespace MGModule\ResellersCenter\models\whmcs\errors;
use MGModule\ResellersCenter as main;

/**
 * Register Error in WHMCS Module Log
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class Register extends main\mgLibs\models\Orm {
    /**
     * Register Exception in WHMCS Module Log
     * 
     * @author Michal Czech <michael@modulesgarden.com>
     * @param Exception $ex
     */
    static function register($ex){
        $token = 'Unknow Token';

        if(method_exists($ex, 'getToken'))
        {
            $token = $ex->getToken();
        }

        $debug = var_export($ex,true);

        \logModuleCall("MGError",__NAMESPACE__,array(
                    'message' => $ex->getMessage()
                    ,'code'   => $ex->getCode()
                    ,'token'  => $token
                ),  $debug,0,0);
    }
}
