<?php
namespace MGModule\ResellersCenter\gateways\Stripe\Components\Interfaces;
/**
 *
 * Created by PhpStorm.
 * User: Tomasz Bielecki ( tomasz.bi@modulesgarden.com )
 * Date: 04.03.20
 * Time: 08:07
 * Class AbstractStripeService
 */
abstract class AbstractStripeService implements Capturable, Validatable
{
    /**
     * @var
     */
    protected $model;

    /**
     * @param $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }


    public static function setApiKey($secretApiKey)
    {
        \Stripe\Stripe::setApiKey($secretApiKey);
    }

}