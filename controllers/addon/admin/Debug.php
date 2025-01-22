<?php

namespace MGModule\ResellersCenter\Controllers\Addon\Admin;

use MGModule\ResellersCenter\core\datatable\DatatableDecorator;
use MGModule\ResellersCenter\mgLibs\Helpers\RelationsCheckHelper;
use MGModule\ResellersCenter\mgLibs\process\AbstractController;
use MGModule\ResellersCenter\models\ResellerService;

class Debug extends AbstractController
{
    public function indexHTML( $input = [] )
    {
        return [
            'tpl'  => 'main',
            'vars' => []
        ];
    }

    public function getDataForTableJSON()
    {
        $resellerClientsWithOneResselerAssigned = RelationsCheckHelper::getClientsWithOneResselerAssigned();

        $scUnassignedHostings      = [];
        $scUnassignedHostingAddons = [];
        $scUnassignedDomains       = [];

        foreach ( $resellerClientsWithOneResselerAssigned as $clientid )
        {
            $hostings = RelationsCheckHelper::getHostingsByClientId($clientid);
            $addons   = RelationsCheckHelper::getHostingAddonsByClientId($clientid);
            $domains  = RelationsCheckHelper::getDomainsByClientId($clientid);

            if ( $hostingIds = RelationsCheckHelper::getUnassignedServices('hosting', $hostings) )
            {
                $scUnassignedHostings[$clientid] = $hostingIds;
            }
            if ( $hostingAddonsIds = RelationsCheckHelper::getUnassignedServices('addon', $addons) )
            {
                $scUnassignedHostingAddons[$clientid] = $hostingAddonsIds;
            }
            if ( $domainIds = RelationsCheckHelper::getUnassignedServices('domain', $domains) )
            {
                $scUnassignedDomains[$clientid] = $domainIds;
            }
        }

        $resselersWithMultipleResselersAssigned = RelationsCheckHelper::getClientsWithMultipleResselersAssigned();

        $mcUnassignedHostings      = [];
        $mcUnassignedHostingAddons = [];
        $mcUnassignedDomains       = [];

        foreach ( $resselersWithMultipleResselersAssigned as $clientid )
        {
            $hostings = RelationsCheckHelper::getHostingsByClientId($clientid);
            $addons   = RelationsCheckHelper::getHostingAddonsByClientId($clientid);
            $domains  = RelationsCheckHelper::getDomainsByClientId($clientid);

            if ( $hostingIds = RelationsCheckHelper::getUnassignedServices('hosting', $hostings) )
            {
                $mcUnassignedHostings[$clientid] = $hostingIds;
            }
            if ( $hostingAddonsIds = RelationsCheckHelper::getUnassignedServices('addon', $addons) )
            {
                $mcUnassignedHostingAddons[$clientid] = $hostingAddonsIds;
            }
            if ( $domainIds = RelationsCheckHelper::getUnassignedServices('domain', $domains) )
            {
                $mcUnassignedDomains[$clientid] = $domainIds;
            }
        }

        $format = [
            'clientId'     => ['link' => ['clientId', 'clientId']],
            'clientName'   => ['link' => ['clientName', 'clientName']],
            'rid'          => ['link' => ['resellerId', 'rid']],
            'resellerName' => ['link' => ['resellerName', 'resellerName']],
            'type'         => ['link' => ['type', 'type']],
            'sid'          => ['link' => ['sid', 'sid']],
            'foundBy'      => ['link' => ['foundBy', 'foundBy']],
            'actions'      => ['link' => ['actions', 'actions']],
        ];

        $buttons = [
            [
                'type'    => 'only-icon',
                'class'   => 'btn-primary fixService',
                'data'    => [
                    'rid'  => 'resellerId',
                    'sid'  => 'sid',
                    'type' => 'type'
                ],
                'text'    => 'Fix Service',
                'tooltip' => 'Adds Service To ResellersCenter_ResellersServices Table'
            ],
            [
                'type'    => 'only-icon',
                'class'   => 'btn-success goToService',
                'data'    => [
                    'cid'  => 'clientId',
                    'sid'  => 'sid',
                    'type' => 'type'
                ],
                'text'    => 'Go to Service',
                'tooltip' => 'Opens service tab'
            ],
        ];

        $sc = RelationsCheckHelper::getVarsForTableSingle($scUnassignedHostings, $scUnassignedHostingAddons, $scUnassignedDomains);
        $mc = RelationsCheckHelper::getVarsForTableMultiple($mcUnassignedHostings, $mcUnassignedHostingAddons, $mcUnassignedDomains);

        $result['data'] = array_merge($sc, $mc);
        $datatable      = new DatatableDecorator($format, $buttons);

        $datatable->parseData($result['data'], count($result['data']), count($result['data']));

        return $datatable->getResult();
    }

    public function fixserviceJSON( $input = [] )
    {
        $service = new ResellerService();
        $service->fillData($input['rid'], $input['sid'], strtolower($input['type']));
        $service->save();
        return ['success' => 'Assigned Service Successfully'];
    }
}