<?php
define(DS, DIRECTORY_SEPARATOR);
use MGModule\ResellersCenter\core\Request;
use MGModule\ResellersCenter\core\invoices\RCInvoice;
use MGModule\ResellersCenter\core\paymentGateway\Factory as GatewayFactory;

//Start WHMCS
require __DIR__.DS.'..'.DS."..".DS."..".DS."..".DS."..".DS."init.php";

//Get loader
require_once __DIR__.DS."..".DS."..".DS."Loader.php";
new \MGModule\ResellersCenter\Loader();

$invoice = new RCInvoice(Request::get("custom"));
$gateway = GatewayFactory::get($invoice->reseller->id, "BillPlzPay");
$gateway->callback($_REQUEST);