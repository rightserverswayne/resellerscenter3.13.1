<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rules;

use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rule;
use MGModule\ResellersCenter\repository\whmcs\Orders;

/**
 * Description of ExistingClient
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ExistingClient extends Rule
{
    /**
     * Check if promotion has any uses left
     *
     * @param $product
     * @return bool
     */
    public function run($product)
    {
        //default
        $result = true;

        //Check if promotion requires existing client
        if($this->promotion->existingclient)
        {
            //if client is logged let's check if he has any active orders
            $result     = false;
            $clientid   = Session::get("uid");
            if($clientid)
            {
                $client = new Client($clientid);
                foreach($client->orders as $order)
                {
                    if($order->status == Orders::STATUS_ACTIVE)
                    {
                        return true;
                    }
                }
            }
        }
        return $result;
    }
}