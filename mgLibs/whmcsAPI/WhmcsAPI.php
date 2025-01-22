<?php

namespace MGModule\ResellersCenter\mgLibs\whmcsAPI;
use MGModule\ResellersCenter as main;
use MGModule\ResellersCenter\repository\whmcs\Configuration as WhmcsConfiguration;
use MGModule\ResellersCenter\core\Logger;
use MGModule\ResellersCenter\Core\Server;

class WhmcsAPI{

    static function getAdmin(){
        static $username;

        if(empty($username))
        {
            $data = main\mgLibs\MySQL\Query::select(
                array('username')
                , 'tbladmins'
                , array()
                , array()
                , 1
            )->fetch();
            $username = $data['username'];
        }

        return $username;
    }

    static function isRcAPiRequest()
    {
        return  strpos($_SERVER['REQUEST_URI'], 'includes/api.php') !== false && $_GET['rcApi'] == 1;
    }

    static function allowToRunLocalApi()
    {
        $isCron         = Server::isRunByCron();
        $currentDomain  = Server::get("HTTP_HOST");
        $whmcsDomain    = Server::getWhmcsDomain();

        //Do poprawy ten warunek
        return true || $isCron || $currentDomain === $whmcsDomain;
    }

    static function request($command,$config)
    {
        //wywalić to jak WHMCS to fixnie kiedyś. Issue #1192: http://git.mglocal/whmcs-products/ResellersCenter3/-/issues/
        if(!function_exists("updateInvoiceTotal"))
        {
            require_once ROOTDIR . "/includes/invoicefunctions.php";
        }

        $result = self::allowToRunLocalApi() ? localAPI($command, $config, self::getAdmin()) : self::sendRequest($command,$config);

        if($result['result'] == 'error' && $result['email'] != 'Email Send Aborted By Hook')
        {
            throw new main\mgLibs\exceptions\WhmcsAPI($result['message']);
        }

        return $result;
    }

    static function sendRequest($command, $config = [])
    {
        $repo = new WhmcsConfiguration();
        $whmcsUrl = $repo->getSetting("SystemURL");
        $whmcsUrl = trim($whmcsUrl, '/').'/';
        
        $RCExternalApiSettings = self::getSettings();

        $config['identifier']   = $RCExternalApiSettings['identifier'];
        $config['secret']       = $RCExternalApiSettings['secret'];
        $config['action']       = $command;
        $config['responsetype'] = 'json';

        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $whmcsUrl . 'includes/api.php?rcApi=1');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($config));

        if(!empty($RCExternalApiSettings['authLogin']) && !empty($RCExternalApiSettings['authPassword']))
        {
            curl_setopt($curl, CURLOPT_USERPWD,  "{$RCExternalApiSettings['authLogin']}:{$RCExternalApiSettings['authPassword']}");
        }

        $response = curl_exec($curl);

        if ($response === false && curl_error($curl)) {
            Logger::error('Unable to connect WHMCS API: ' . curl_errno($curl) . ' - ' . curl_error($curl));
            throw new main\mgLibs\exceptions\WhmcsAPI('Unable to connect WHMCS API: Please contact administrator.');
        }

        $info = curl_getinfo($curl);
        if($info['http_code'] != 200)
        {
            $errorResult    = json_decode($response, true);
            $errorInfo           = isset($errorResult['result']) && $errorResult['result'] == 'error' ? $errorResult['message'] : "Unknown error";
            Logger::error('Unable to connect WHMCS API: ' . $info['http_code'] . ' error. '. $errorInfo);
            throw new main\mgLibs\exceptions\WhmcsAPI('Unable to connect WHMCS API: Please contact administrator.');
        }

        curl_close($curl);

        $result = json_decode($response, true);
        return $result;
    }

    static function getSettings()
    {
        $repo = new WhmcsConfiguration();
        $RCExternalApiSettings = json_decode($repo->getSetting("RCExternalApiSettings"), true);

        $whmcsApiSettings['identifier']     = $RCExternalApiSettings['identifier'] ?: "";
        $whmcsApiSettings['secret']         = $RCExternalApiSettings['secret'] ? \decrypt($RCExternalApiSettings['secret']) : "";
        $whmcsApiSettings['authLogin']      = $RCExternalApiSettings['authLogin'] ?: "";
        $whmcsApiSettings['authPassword']   = $RCExternalApiSettings['authPassword'] ? \decrypt($RCExternalApiSettings['authPassword']) : "";

        return $whmcsApiSettings;
    }

    static function saveSettings($whmcsApiSettings)
    {
        global $cc_encryption_hash;

        $RCExternalApiSettings['identifier']    = $whmcsApiSettings['identifier'] ?: "";
        $RCExternalApiSettings['secret']        = $whmcsApiSettings['secret'] ? \encrypt($whmcsApiSettings['secret'], $cc_encryption_hash) : "";
        $RCExternalApiSettings['authLogin']     = $whmcsApiSettings['authLogin'] ?: "";
        $RCExternalApiSettings['authPassword']  = $whmcsApiSettings['authPassword'] ? \encrypt($whmcsApiSettings['authPassword'], $cc_encryption_hash) : "";

        $repo = new WhmcsConfiguration();
        $repo->saveSetting("RCExternalApiSettings", json_encode($RCExternalApiSettings));
    }

    static function getAdminDetails($adminId){

        $data = main\mgLibs\MySQL\Query::select(
            array('username')
            , 'tbladmins'
            , array("id" =>$adminId )
            , array()
            , 1
        )->fetch();
        $username = $data['username'];

        $result = localAPI("getadmindetails",array(),$username);
        if($result['result'] == 'error')
            throw new main\mgLibs\exceptions\WhmcsAPI($result['message']);

        $result['allowedpermissions'] = explode(",", $result['allowedpermissions']);
        return  $result;
    }
}
