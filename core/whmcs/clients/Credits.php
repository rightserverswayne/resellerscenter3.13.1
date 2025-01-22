<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Clients;

use MGModule\ResellersCenter\mgLibs\whmcsAPI\WhmcsAPI;

/**
 * Description of Credits
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Credits
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Load client's credits object
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param $amount
     * @param $description
     * @param int $relid
     * @throws \MGModule\ResellersCenter\mgLibs\exceptions\WhmcsAPI
     */
    public function add($amount, $description, $relid = 0)
    {
        $type   = $amount > 0 ? 'add' : 'remove';
        $amount = abs($amount);

        WhmcsAPI::request("AddCredit", [
            "clientid"    => $this->client->id,
            "amount"      => $amount,
            "description" => $description,
            'type'        => $type
        ]);
    }

    /**
     * Remove credits from client's account
     *
     * @param $amount
     * @param $description
     */
    public function remove($amount, $description)
    {
        //Remove credits
        $this->client->credit -= $amount;
        $this->client->save();


        //Add info about removing credits
        $repo = new \MGModule\ResellersCenter\repository\whmcs\Credits();
        $repo->create([
            "clientid"      => $this->client->id,
            "admin_id"      => 0,
            "date"          => date("Y-m-d"),
            "description"   => $description,
            "amount"        => (-1) * $amount,
            "relid"         => 0
        ]);
    }
}