<?php
namespace MGModule\ResellersCenter\Core\Whmcs\Products;

use MGModule\ResellersCenter\Core\Whmcs\Products\addons\Addon;
use MGModule\ResellersCenter\Core\Whmcs\Products\domains\Domain;
use MGModule\ResellersCenter\Core\Whmcs\Products\Products\Product;

/**
 * Abstract item list to store Products, Addons and Domain
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ServiceList implements \Iterator
{
    private $position;

    private $elements = [];

    /**
     * Insert new object to list
     * 
     * @param type $object
     * @throws \Exception
     */
    public function add($object)
    {
        //Check object type
        if($object instanceof Addon || $object instanceof Domain || $object instanceof Product)
        {
            $this->elements[] = $object;
        }
        else
        {
            $classname = get_class($object);
            throw new \Exception("Invalid object type {$classname} provided");
        }
    }
    
    public function delete($elementid)
    {
        if(isset($this->elements[$elementid])) 
        {
            unset($this->elements[$elementid]);
            $this->reorder();
            $this->position--;
        }
        else
        {
            throw new \Exception("Item has not been found on the list");
        }
    }
    
    public function reorder()
    {
        $new = array();
        foreach($this->elements as $element)
        {
            $new[] = $element;
        }

        $this->elements = $new;
    }
    
    public function find($key, $value)
    {
        $results = array();
        foreach($this->elements as $element)
        {
            if($element->{$key} == $value)
            {
                $results[] = $element;
            }
        }
        
        return $results;
    }
    
    public function findOne($key, $value)
    {
        $result = $this->find($key, $value);
        return $result[0];
    }

    public function __construct()
    {
        $this->position = 0;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->elements[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->elements[$this->position]);
    }
}