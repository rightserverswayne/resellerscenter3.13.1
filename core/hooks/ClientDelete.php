<?php
namespace MGModule\ResellersCenter\core\hooks;

use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;

/**
 * Description of ClientDelete
 *
 * @author Paweł Złamaniec
 */
class ClientDelete
{
    /**
     * Array of anonymous functions.
     * Array keys are hooks priorities.
     *
     * @var array
     */
    public $functions;

    /**
     * Assign anonymous function
     */
    public function __construct()
    {
        $this->functions[0] = function($params)
        {
            return $this->deleteResellerRelations($params);
        };
    }

    public function deleteResellerRelations($params)
    {
        $client = new Client($params["userid"]);
        $reseller = $client->getReseller();

        $relation = $reseller->clients->find($params["userid"]);
        if($relation->exists)
        {
            $reseller->clients->unassign($params["userid"]);
        }
    }
}