<?php

namespace MGModule\ResellersCenter\mgLibs\Helpers;

use MGModule\ResellersCenter\models\ResellerClient;
use MGModule\ResellersCenter\models\ResellerService;
use MGModule\ResellersCenter\models\whmcs\Client;
use MGModule\ResellersCenter\models\whmcs\Domain;
use MGModule\ResellersCenter\models\whmcs\Hosting;
use MGModule\ResellersCenter\models\whmcs\HostingAddon;

/**
 * Class RelationsCheckHelper
 * @package MGModule\ResellersCenter\mgLibs\Helpers
 */
class RelationsCheckHelper
{

    static public $clients = [];
    static public $resellers = [];

    /**
     * Selects clients that exists only once in Reseller Clients table
     * @return array
     */
    public static function getClientsWithOneResselerAssigned()
    {
        return (new ResellerClient())
            ->select('client_id')
            ->havingRaw('COUNT(client_id) = 1')
            ->groupBy('client_id')
            ->pluck('client_id')
            ->toArray();
    }

    /**
     * Returns clients mentioned more than once in Reseller Clients table
     * @return array
     */
    public static function getClientsWithMultipleResselersAssigned()
    {
        return (new ResellerClient())
            ->select('client_id')
            ->havingRaw('COUNT(client_id) > 1')
            ->groupBy('client_id')
            ->pluck('client_id')
            ->toArray();
    }

    /**
     * Returns array of hosting ids owned by client
     * @param int $clientid
     * @return array
     */
    public static function getHostingsByClientId( $clientid )
    {
        return Hosting::where('userid', $clientid)
            ->select('id')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Returns array of hosting addon ids owned by client
     * @param int $clientid
     * @return array
     */
    public static function getHostingAddonsByClientId( $clientid )
    {
        return HostingAddon::where('userid', $clientid)
            ->select('id')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Returns array of domain ids owned by client
     * @param int $clientid
     * @return array
     */
    public static function getDomainsByClientId( $clientid )
    {
        return Domain::where('userid', $clientid)
            ->select('id')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Returns array of service ids not found in database
     * @param string $type       allowed types: addon|hosting|domain
     * @param array  $serviceIds array of ids of specified service type
     * @return array of ids that don't exist in table
     */
    public static function getUnassignedServices( $type, $serviceIds = [] )
    {
        $found = ResellerService::where('type', $type)
            ->whereIn('relid', $serviceIds)
            ->select('relid')
            ->pluck('relid')
            ->toArray();

        return array_diff($serviceIds, $found);
    }

    /**
     * Returns array with all necesery data to display in table, works for clients with one reseller assigned
     * @param array $scUnassignedHostings
     * @param array $scUnassignedHostingAddons
     * @param array $scUnassignedDomains
     * @return array
     */
    public static function getVarsForTableSingle( array $scUnassignedHostings = [], array $scUnassignedHostingAddons = [], array $scUnassignedDomains = [] )
    {
        $out = [];
        foreach ( $scUnassignedHostings as $clientId => $hostingIds )
        {
            $client          = isset(static::$clients[$clientId]) ? static::$clients[$clientId] : static::getAndCacheClientName($clientId);
            $guessedReseller = ResellerClient::where('client_id', $clientId)->select('reseller_id')->get()->first()->reseller_id;

            $reseller = isset(static::$resellers[$guessedReseller]) ? static::$resellers[$guessedReseller] : static::getAndCacheResellerName($guessedReseller);

            foreach ( $hostingIds as $hostingId )
            {
                $out[] = [
                    'clientId'     => $clientId,
                    'clientName'   => $client,
                    'resellerId'   => $guessedReseller,
                    'resellerName' => $reseller,
                    'type'         => 'Hosting',
                    'sid'          => $hostingId,
                    'foundBy'      => 'Client ID'
                ];
            }
        }

        foreach ( $scUnassignedHostingAddons as $clientId => $hostingAddonIds )
        {
            $client          = isset(static::$clients[$clientId]) ? static::$clients[$clientId] : static::getAndCacheClientName($clientId);
            $guessedReseller = ResellerClient::where('client_id', $clientId)->select('reseller_id')->get()->first()->reseller_id;

            $reseller = isset(static::$resellers[$guessedReseller]) ? static::$resellers[$guessedReseller] : static::getAndCacheResellerName($guessedReseller);

            foreach ( $hostingAddonIds as $hostingAddonId )
            {
                $out[] = [
                    'clientId'     => $clientId,
                    'clientName'   => $client,
                    'resellerId'   => $guessedReseller,
                    'resellerName' => $reseller,
                    'type'         => 'Addon',
                    'sid'          => $hostingAddonId,
                    'foundBy'      => 'Client ID'
                ];
            }
        }

        foreach ( $scUnassignedDomains as $clientId => $domainIds )
        {
            $client          = isset(static::$clients[$clientId]) ? static::$clients[$clientId] : static::getAndCacheClientName($clientId);
            $guessedReseller = ResellerClient::where('client_id', $clientId)->select('reseller_id')->get()->first()->reseller_id;

            $reseller = isset(static::$resellers[$guessedReseller]) ? static::$resellers[$guessedReseller] : static::getAndCacheResellerName($guessedReseller);

            foreach ( $domainIds as $domainId )
            {
                $out[] = [
                    'clientId'     => $clientId,
                    'clientName'   => $client,
                    'resellerId'   => $guessedReseller,
                    'resellerName' => $reseller,
                    'type'         => 'Domain',
                    'sid'          => $domainId,
                    'foundBy'      => 'Client ID'
                ];
            }
        }

        return $out;
    }

    /**
     * Creates array ready to be parsed and guesses the resellers
     * @param array $mcUnassignedHostings
     * @param array $mcUnassignedHostingAddons
     * @param array $mcUnassignedDomains
     * @return array of arrays with all necesery data to create datatable
     */
    public static function getVarsForTableMultiple( array $mcUnassignedHostings, array $mcUnassignedHostingAddons, array $mcUnassignedDomains )
    {
        $out = [];
        foreach ( $mcUnassignedHostings as $clientId => $hostingIds )
        {
            $client = isset(static::$clients[$clientId]) ? static::$clients[$clientId] : static::getAndCacheClientName($clientId);
            foreach ( $hostingIds as $hostingId )
            {
                $resselerId = static::tryToGuessResellerByHosting($hostingId);

                $foundBy = 'Connected Service';

                if ( !$resselerId )
                {
                    $resselerId = ResellerClient::where('client_id', $clientId)->select('reseller_id')->get()->first()->reseller_id;
                    $foundBy    = 'No connected services displaying first assigned reseller';
                }

                $reseller = isset(static::$resellers[$resselerId]) ? static::$resellers[$resselerId] : static::getAndCacheResellerName($resselerId);

                $out[] = [
                    'clientId'     => $clientId,
                    'clientName'   => $client,
                    'resellerId'   => $resselerId,
                    'resellerName' => $reseller,
                    'type'         => 'Hosting',
                    'sid'          => $hostingId,
                    'foundBy'      => $foundBy
                ];
            }
        }

        foreach ( $mcUnassignedHostingAddons as $clientId => $hostingAddonIds )
        {
            $client = isset(static::$clients[$clientId]) ? static::$clients[$clientId] : static::getAndCacheClientName($clientId);
            foreach ( $hostingAddonIds as $hostingAddonId )
            {
                $resselerId = static::tryToGuessResellerByHostingAddon($hostingAddonId);
                $foundBy    = 'Connected Service';

                if ( !$resselerId )
                {
                    $resselerId = ResellerClient::where('client_id', $clientId)->select('reseller_id')->get()->first()->reseller_id;
                    $foundBy    = 'No connected services picked first';
                }

                $reseller = isset(static::$resellers[$resselerId]) ? static::$resellers[$resselerId] : static::getAndCacheResellerName($resselerId);

                $out[] = [
                    'clientId'     => $clientId,
                    'clientName'   => $client,
                    'resellerId'   => $resselerId,
                    'resellerName' => $reseller,
                    'type'         => 'Addon',
                    'sid'          => $hostingAddonId,
                    'foundBy'      => $foundBy
                ];
            }
        }

        foreach ( $mcUnassignedDomains as $clientId => $domainIds )
        {
            $client = isset(static::$clients[$clientId]) ? static::$clients[$clientId] : static::getAndCacheClientName($clientId);
            foreach ( $domainIds as $domainId )
            {
                $resselerId = static::tryToGuessResellerByDomain($domainId);
                $foundBy    = 'Connected Service';

                if ( !$resselerId )
                {
                    $resselerId = ResellerClient::where('client_id', $clientId)->select('reseller_id')->get()->first()->reseller_id;
                    $foundBy    = 'No connected services picked first';
                }

                $reseller = isset(static::$resellers[$resselerId]) ? static::$resellers[$resselerId] : static::getAndCacheResellerName($resselerId);

                $out[] = [
                    'clientId'     => $clientId,
                    'clientName'   => $client,
                    'resellerId'   => $resselerId,
                    'resellerName' => $reseller,
                    'type'         => 'Domain',
                    'sid'          => $domainId,
                    'foundBy'      => $foundBy
                ];
            }
        }
        return $out;
    }

    /**
     * Tries to locate the resseller by connected services with hosting id
     * @param $hostingId
     * @return int|null
     */
    public static function tryToGuessResellerByHosting( $hostingId )
    {
        $domainName = Hosting::where('id', $hostingId)->select('domain')->first()->domain;

        $domain = Domain::where('domain', $domainName)->select('id')->get();

        if ( $domain->count() )
        {
            $domainId     = $domain->first()->id;
            $targetDomain = ResellerService::where('type', 'domain')->where('relid', $domainId)->select('reseller_id')->get();

            if ( $targetDomain->count() )
            {
                return $targetDomain->first()->reseller_id;
            }
        }

        $addon = HostingAddon::where('hostingid', $hostingId)->select('id')->get();

        if ( $addon->count() )
        {
            $addonId     = $addon->first()->id;
            $targetAddon = ResellerService::where('type', 'addon')->where('relid', $addonId)->select('reseller_id')->get();
            if ( $targetAddon->count() )
            {
                return $targetAddon->first()->reseller_id;
            }
        }

        return null;
    }

    /**
     * Tries to locate the resseller by connected services with hosting addon id
     * @param $hostingAddonId
     * @return int|null
     */
    public static function tryToGuessResellerByHostingAddon( $hostingAddonId )
    {
        $connectedHostingId = HostingAddon::where('id', $hostingAddonId)->select('hostingid')->get()->first()->hostingid;

        $hosting = ResellerService::where('type', 'hosting')->where('relid', $connectedHostingId)->select('reseller_id')->get();

        if ( $hosting->count() )
        {
            return $hosting->first()->reseller_id;
        }

        $domainName = Hosting::where('id', $connectedHostingId)->select('domain')->get()->first()->domain;
        $domain     = Domain::where('domain', $domainName)->select('id')->get();

        if ( $domain->count() )
        {
            $domainId = $domain->first()->id;

            $targetDomain = ResellerService::where('type', 'domain')->where('relid', $domainId)->select('reseller_id')->get();

            if ( $targetDomain->count() )
            {
                return $targetDomain->first()->reseller_id;
            }
        }

        return null;
    }

    /**
     * Tries to find client by matching services with domain id
     * @param int $domainId
     * @return int|null
     */
    public static function tryToGuessResellerByDomain( $domainId )
    {
        $domainName = Domain::where('id', $domainId)->select('domain')->get()->first()->domain;
        $hosting    = Hosting::where('domain', $domainName)->select('id')->get();

        if ( $hosting->count() )
        {
            $hostingId     = $hosting->first()->id;
            $targetHosting = ResellerService::where('type', 'hosting')->where('relid', $hostingId)->select('reseller_id')->get();

            if ( $targetHosting->count() )
            {
                return $targetHosting->first()->reseller_id;
            }

            $hostingAddon = HostingAddon::where('hostingid', $hostingId)->select('id')->get();

            if ( $hostingAddon->count() )
            {
                $hostingAddonId = $hostingAddon->first()->id;

                $targetHostingAddon = ResellerService::where('type', 'addon')->where('relid', $hostingAddonId)->select('reseller_id')->get();

                if ( $targetHostingAddon->count() )
                {
                    return $targetHostingAddon->first()->reseller_id;
                }
            }
        }

        return null;
    }

    /**
     * Gets concated name first and last name of client
     * @param $clientId
     * @return string
     */
    private static function getAndCacheClientName( $clientId )
    {
        $clientName = Client::where('id', $clientId)->selectRaw("CONCAT (`firstname`,' ',`lastname`) as name")->get();

        if ( !$clientName->count() )
        {
            $clientName = 'Foo Bar';
        }
        else
        {
            $clientName = $clientName->first()->name;
        }

        static::$clients[$clientId] = $clientName;

        return $clientName;
    }

    /**
     * @param $resellerId
     * @return string
     */
    private static function getAndCacheResellerName( $resellerId )
    {
        $resellerName = Client::where('id', $resellerId)->selectRaw("CONCAT (`firstname`,' ',`lastname`) as name")->get();

        if ( !$resellerName->count() )
        {
            $resellerName = 'Foo Bar';
        }
        else
        {
            $resellerName = $resellerName->first()->name;
        }

        static::$resellers[$resellerId] = $resellerName;

        return $resellerName;
    }
}