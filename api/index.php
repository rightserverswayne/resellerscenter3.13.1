<?php
$modulesDit = dirname(dirname(dirname(__FILE__)));

require_once $modulesDit . '/../../init.php';
require_once '../Loader.php';

use MGModule\ResellersCenter\Helpers\JsonExport;
use MGModule\ResellersCenter\repository\ResellersSettings;

$data = [];
try
{
    $type      = $_REQUEST['report'];
    $relid     = $_REQUEST['resid'];
    $httpToken = $_REQUEST['token'];

    $repo      = new ResellersSettings();
    $baseToken = $repo->getSetting('token', 0);

    if (empty($baseToken))
    {
        throw new \Exception('Token not set in module configuration');
    }

    if ($httpToken !== $baseToken)
    {
        throw new \Exception('Invalid token');
    }

    $exporter = new JsonExport($type, $relid);
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