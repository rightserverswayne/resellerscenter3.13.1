<?php

namespace MGModule\ResellersCenter\gateways\Stripe\Components\Interfaces;


/**
 *
 * Created by PhpStorm.
 * User: Tomasz Bielecki ( tomasz.bi@modulesgarden.com )
 * Date: 04.03.20
 * Time: 09:12
 * Class AbstractRequestModel
 */
abstract class AbstractRequestModel
{
    /**
     * @var array
     */
    protected $requestStorage = [];

    /**
     * @param array $requests
     * @return AbstractRequestModel
     */
    public static function build($requests = [])
    {
        $model = new static();
        $model->fill($requests);
        $model->setRequestStorage($requests);

        return $model;
    }

    /**
     * @param array $request
     * @return $this
     */
    public function setRequestStorage($request = [])
    {
        $this->requestStorage = $request;
        return $this;
    }

    public function getRequestStorage()
    {
        return $this->requestStorage;
    }
    /**
     * @param array $data
     * @return $this
     */
    protected function fill($data = [])
    {
        if (!is_array($data))
        {
            $data = get_object_vars($data);
        }
        foreach ($data as $key => $val)
        {
            if (property_exists($this, $key) === false)
            {
                if (strpos($key, '_') !== false)
                {
                    $explode    = explode('_', $key);
                    $ucfirstAll = array_map('ucfirst', $explode);
                    $fixedKey   = lcfirst(implode('', $ucfirstAll));
                    if (property_exists($this, $fixedKey))
                    {
                        $this->$fixedKey = (is_array($val)) ? $val : (string) $val;
                    }

                    continue;
                }

                if (strpos($key, '-') !== false)
                {
                    $explode    = explode('-', $key);
                    $ucfirstAll = array_map('ucfirst', $explode);
                    $fixedKey   = lcfirst(implode('', $ucfirstAll));
                    if (property_exists($this, $fixedKey))
                    {
                        $this->$fixedKey = (is_array($val)) ? $val : (string) $val;
                    }

                    continue;
                }

                continue;
            }

            $this->$key = (is_array($val)) ? $val : (string) $val;
        }

        return $this;
    }

}