<?php

namespace MGModule\ResellersCenter\Core\Traits;

/**
 * Description of IsResellerProperty
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */

trait IsFlattened
{
    /**
     * Object data
     *
     * @var mixed
     */
    protected $data;

    /**
     * Method to fill $data parameter
     *
     * @return mixed
     */
    abstract protected function getData();

    /**
     * Get param data
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        $this->data = $this->data ?: $this->getData();

        /**
         * Check if value is serialized
         * using @ to suppress warning when variable is not serialized
         */
        $unserialized = @unserialize($this->data[$name]);
        if ($this->data[$name] !== 'b:0;' && $unserialized !== false)
        {
            return $unserialized;
        }

        return $this->data[$name];
    }

    /**
     * Set param data
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->data = $this->data ?: $this->getData();
        $this->data[$name] = $value;
    }
}