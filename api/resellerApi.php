<?php
$modulesDit = dirname(dirname(dirname(__FILE__)));

require_once $modulesDit . '/../../init.php';
require_once '../Loader.php';

use MGModule\ResellersCenter\Helpers\JsonExport;
use MGModule\ResellersCenter\repository\ResellersSettings;
use \MGModule\ResellersCenter\models\whmcs\Client;
use \MGModule\ResellersCenter\models\Reseller;

$data = [];
try
{
    $type      = $_REQUEST['report'];
    $email     = $_REQUEST['email'];
    $httpToken = $_REQUEST['token'];

    //check if reseller exists
    $client     = Client::where('email', $email)->first();
    $reseller   = Reseller::where('client_id', $client->id ?: 0)->first();

    if(!$reseller)
    {
        throw new \Exception("Reseller with this email address doesn't exists");
    }

    $repo      = new ResellersSettings();
    $baseToken = $repo->getSetting('apikey', $reseller->id, true);

    if (empty($baseToken))
    {
        throw new \Exception('Token not set in configuration');
    }

    if ($httpToken !== $baseToken)
    {
        throw new \Exception('Invalid token');
    }

    $exporter = new JsonExport($type, $reseller->id);
    $data     = $exporter->getJson();
}
catch (\Exception $ex)
{
    $data = ['result' => 'error', 'message' => $ex->getMessage()];
    logModuleCall('ResellersCenter', 'getReport', $data['message'], $data['result']);
}

ob_clean();
header('Content-Type: application/json');
echo json_encode($data);
exit;
