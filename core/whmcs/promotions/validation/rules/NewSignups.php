<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rules;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rule;
use MGModule\ResellersCenter\repository\whmcs\Orders;

/**
 * Description of NewSignups
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class NewSignups extends Rule
{
    /**
     * Check if promotion has any uses left
     *
     * @param $product
     * @return bool
     */
    public function run($product)
    {
        $result = true;
        if($this->promotion->newsignups)
        {
            //if client is logged let's check if he has any active orders
            $clientid = Session::get("uid");
            if(!$clientid)
            {
                $client = new Client($clientid);
                foreach($client->orders as $order)
                {
                    if($order->status != Orders::STATUS_PENDING)
                    {
                        $result = false;
                    }
                }
            }
        }

        return $result;
    }
}