<?php
// Reference: http://docs.whmcs.com/Using_Models
// Require libraries needed for gateway module functions.
require('../../../../../../init.php');
include('../../../../../../includes/functions.php');
include('../../../../../../includes/gatewayfunctions.php');
require_once __DIR__ . '/billplz-api.php';

// Get variable for WHMCS configuration
global $CONFIG;


// Module name.
$reseller = \MGModule\ResellersCenter\Core\Helpers\Reseller::getCurrent();
$gateway = \MGModule\ResellersCenter\Core\Resources\Gateways\Factory::get( $reseller->id, "BillPlzPay");


// Die if module is not active.
if (!$gateway->enabled)
{
    die("Module Not Activated");
}

// Get payment gateway details
$api_key = $gateway->apiKey;
$x_signature = $gateway->xSignatureKey;

// Retrieve data returned in payment gateway return

try
{
    $data = Billplz::getRedirectData($x_signature);
}
catch (\Exception $e)
{
    exit($e->getMessage());
}

$billplz = new Billplz($api_key);

// Validate the status from ID
$moreData = $billplz->check_bill($data['id']);

// Collect data
$success = $data['paid'];
$invoiceId = $moreData['reference_1'];
$transactionId = $data['id'];
$paymentAmount = number_format(($moreData['amount'] / 100), 2);
$paymentFee = 0;
$hash = Billplz::getSignature();

/*
* Get Base Currency Amount
*/
if (!empty($moreData['reference_2'])) {
    $paymentAmount = $moreData['reference_2'];
}

$transactionState = $moreData['state'];
if ($success) {
    /**
     * Validate Callback Invoice ID.
     *
     * Checks invoice ID is a valid invoice number. Note it will count an
     * invoice in any status as valid.
     *
     * Performs a die upon encountering an invalid Invoice ID.
     *
     * Returns a normalized invoice ID.
     */

    /**
     * Check Billplz Bills ID.
     *
     * Performs a check for any existing transactions with the same given
     * transaction number.
     *
     * Don't update to db if already have.
     */
    $sql = \MGModule\ResellersCenter\models\Transaction::where('transid', $data['id'])->get();
    foreach ($sql as $data)
    {
        $result = $data->transid;
        break;
    }

    if (empty($result))
    {
        $transactionStatus = 'Return: ' . $transactionState;

        /**
         * Add Invoice Payment.
         *
         * Applies a payment transaction entry to the given invoice ID.
         *
         * @param int $invoiceId         Invoice ID
         * @param string $transactionId  Transaction ID
         * @param float $paymentAmount   Amount paid (defaults to full balance)
         * @param float $paymentFee      Payment fee (optional)
         * @param string $gatewayModule  Gateway module name
         */
        $invoice = new \MGModule\ResellersCenter\Core\Resources\Invoices\Invoice($invoiceId);
        if($gateway->convertto)
        {
            $paymentAmount = convertCurrency($paymentAmount, $gateway->convertto, $invoice->client->currency);
        }

        $invoice->payments->addTransaction(0, $transactionId, $paymentAmount, $paymentFee, $gateway->name);

        // Log Transaction
        $gateway->log($_GET, $transactionStatus);
    }

    global $CONFIG;
    $parsed = parse_url($CONFIG["SystemURL"]);
    $script = \MGModule\ResellersCenter\core\Server::get("SCRIPT_NAME");
    $path = substr($script, 0, strpos($script, $parsed["path"]) + strlen($parsed["path"]));
    $systemUrl = parse_url(\MGModule\ResellersCenter\core\Server::getCurrentSystemURL());
    $url = "{$systemUrl["scheme"]}://{$systemUrl["host"]}{$path}/";

    // Get success redirection path
    if ($gateway->successPath == 'viewinvoice')
    {
        $redirect_url = $url . 'rcviewinvoice.php?id=' . $invoiceId;
    }
    elseif ($gateway->successPath == 'listinvoice')
    {
        $redirect_url = $url . "clientarea.php?action=invoices";
    } else
    {
        $redirect_url = $url . "clientarea.php";
    }
}
else
{

    // Get failed redirection path
    if ($gateway->failedPath == 'viewinvoice')
    {
        $redirect_url = $url . 'rcviewinvoice.php?id=' . $invoiceId;
    }
    elseif ($gateway->failedPath == 'listinvoice')
    {
        $redirect_url = $url . "clientarea.php?action=invoices";
    }
    else
    {
        $redirect_url = $url . "clientarea.php";
    }
}
header("Location: " . $redirect_url);
