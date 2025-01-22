<?php

namespace MGModule\ResellersCenter\Core\Traits;

/**
 * Description of Iterator
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */

trait Iterator
{
    /**
     * Current position in the array
     *
     * @var int
     */
    private $iteratorPosition = 0;

    /**
     * Object property name that will be used to iterate
     *
     * @var string
     */
    private $iteratorArrayName;

    /**
     * Set array name
     *
     * @param $name
     */
    protected function initIteratorSetArrayName($name)
    {
        $this->iteratorArrayName = $name;
    }

    public function rewind()
    {
        $this->iteratorPosition = 0;
    }

    public function current()
    {
        $array = array_values($this->{$this->iteratorArrayName});
        return $array[$this->iteratorPosition];
    }

    public function key()
    {
        $keys = $array = array_keys($this->{$this->iteratorArrayName});
        return $keys[$this->iteratorPosition];
    }

    public function next()
    {
        ++$this->iteratorPosition;
    }

    public function valid()
    {
        if($this->{$this->iteratorArrayName})
        {
            $array = array_values($this->{$this->iteratorArrayName});
        }

        return isset($array[$this->iteratorPosition]);
    }
}