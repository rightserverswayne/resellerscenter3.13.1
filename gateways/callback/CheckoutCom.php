<?php
define(DS, DIRECTORY_SEPARATOR);

//Start WHMCS
require __DIR__.DS.'..'.DS."..".DS."..".DS."..".DS."..".DS."init.php";

//Get loader
require_once __DIR__.DS."..".DS."..".DS."Loader.php";
new \MGModule\ResellersCenter\Loader();

$reseller = MGModule\ResellersCenter\core\helpers\Reseller::getCurrent();
$gateway  = MGModule\ResellersCenter\core\resources\gateways\Factory::get($reseller->id, "CheckoutCom");

$gateway->callback($_REQUEST);