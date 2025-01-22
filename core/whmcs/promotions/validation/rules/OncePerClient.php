<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rules;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rule;

/**
 * Description of OncePerClient
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class OncePerClient extends Rule
{
    /**
     * Check if promotion was not applied before for the same client
     *
     * @param $product
     * @return bool
     */
    public function run($product)
    {
        $result = true;
        if($this->promotion->onceperclient)
        {
            //if client is logged let's check if he has any active orders
            $clientid = Session::get("uid");
            if(!$clientid)
            {
                $client = new Client($clientid);
                foreach($client->orders as $order)
                {
                    if($order->promocode == $this->promotion->code)
                    {
                        $result = false;
                    }
                }
            }
        }

        return $result;
    }
}