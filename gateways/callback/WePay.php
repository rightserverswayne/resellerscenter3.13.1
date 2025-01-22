<?php
define(DS, DIRECTORY_SEPARATOR);
use \MGModule\ResellersCenter\core\Request;
use \MGModule\ResellersCenter\Core\Resources\Invoices\Invoice;
use \MGModule\ResellersCenter\core\resources\gateways\Factory as GatewayFactory;

//Start WHMCS
require __DIR__.DS.'..'.DS."..".DS."..".DS."..".DS."..".DS."init.php";

//Get loader
require_once __DIR__.DS."..".DS."..".DS."Loader.php";
new \MGModule\ResellersCenter\Loader();


$invoice = new Invoice(Request::get("reference_id"));
$gateway = GatewayFactory::get($invoice->reseller->id, "WePay");

$gateway->callback($_REQUEST);