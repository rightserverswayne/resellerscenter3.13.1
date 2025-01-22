<?php
use \Illuminate\Database\Capsule\Manager as DB;

if(!defined('DS'))define('DS',DIRECTORY_SEPARATOR);

//WHMCS is loading hooks before _upgrade function...
$ver = DB::table("tbladdonmodules")->where("module", "ResellersCenter")->where("setting", "version")->first();
if(version_compare($ver->value, "3.0.0") >= 0)
{
    require __DIR__.DS."Loader.php";
    new \MGModule\ResellersCenter\Loader();

    new MGModule\ResellersCenter\core\HookManager();
}