<?php
namespace MGModule\ResellersCenter\gateways\Stripe\Components\Submodule;
use MGModule\ResellersCenter\gateways\Stripe\Components\Interfaces\AbstractStripeService;
use MGModule\ResellersCenter\gateways\Stripe\Components\Submodule\Stripe3\StripeServices;

/**
 *
 * Created by PhpStorm.
 * User: Tomasz Bielecki ( tomasz.bi@modulesgarden.com )
 * Date: 04.03.20
 * Time: 08:05
 * Class SubmoduleFactory
 */
class SubmoduleFactory
{

    const STRIPE_VERSION_3 = 'stripe3';

    /**
     * @param $submodule
     * @return AbstractStripeService
     */
    public static function create($submodule)
    {

        switch ($submodule)
        {
            case static::STRIPE_VERSION_3:
                $submodule = new StripeServices();
                break;
            default:
                $submodule = new StripeServices();
                break;
        }

        return $submodule;
    }
}