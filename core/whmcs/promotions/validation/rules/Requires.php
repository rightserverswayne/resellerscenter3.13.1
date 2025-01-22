<?php

namespace MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rules;

use MGModule\ResellersCenter\core\helpers\DomainHelper;
use MGModule\ResellersCenter\Core\Session;
use MGModule\ResellersCenter\Core\Whmcs\Clients\Client;
use MGModule\ResellersCenter\Core\Whmcs\Products\Domains\Domain;
use MGModule\ResellersCenter\Core\Whmcs\Promotions\Validation\Rule;

/**
 * Description of Requires
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Requires extends Rule
{
    /**
     * Check if client has necessary products in cart or already in his account
     *
     * @param $product
     * @return bool
     */
    public function run($product)
    {
        $result = true;

        $requires = $this->promotion->getRequires();
        foreach($requires as $requireid => $require)
        {
            //Check cart
            if(!in_array($requireid, $this->getFromCart()))
            {
                $result = false;
            }

            //Check client account if promotion settings allows that
            if($result == false && $this->promotion->requiresexisting)
            {
                if(in_array($requireid, $this->getFromClient()))
                {
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * Get cart products
     *
     * @return array
     */
    protected function getFromCart()
    {
        $result = [];

        //Products
        $products = Session::get("cart.products");
        foreach($products as $product)
        {
            $result[] = $product["pid"];
            foreach($product["addons"] as $addonid)
            {
                $result[] = "A{$addonid}";
            }
        }

        //Addons
        $addons = Session::get("cart.addons");
        foreach($addons as $addon)
        {
            $aid = "A{$addon["id"]}";
            if(!in_array($aid, $result))
            {
                $result[] = $aid;
            }
        }

        //Domains
        $domains = Session::get("cart.domains");
        foreach($domains as $raw)
        {
            $helper = new DomainHelper($raw["domain"]);
            $domain = new Domain($helper->getTLD());
            if($domain->exists)
            {
                $result[] = "D{$domain->extension}";
            }
        }

        return $result;
    }

    /**
     * Get product that are already exists on client's account
     *
     * @return array
     */
    protected function getFromClient()
    {
        $result     = [];
        $clientid   = Session::get("uid");

        //If the order is made for an existing client
        if($clientid)
        {
            $client = new Client($clientid);

            //Products
            foreach($client->hostings as $hosting)
            {
                $result[] = $hosting->packageid;
            }

            //Addons
            foreach($client->hostingAddons as $addon)
            {
                $result[] = "A{$addon->addonid}";
            }

            //Domains
            foreach($client->domains as $domain)
            {
                $helper     = new DomainHelper($domain->domain);
                $extension  = new Domain($helper->getTLD());
                $result[]   = "D{$extension->extension}";
            }
        }

        return $result;
    }
}