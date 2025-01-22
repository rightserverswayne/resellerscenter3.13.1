<?php

namespace MGModule\ResellersCenter\Core\Resources\Invoices;

/**
 * Description of ItemsList.php
 *
 * @author Paweł Zlamaniec <pawel.zl@modulesgarden.com>
 */
class ItemsList implements \Iterator
{
    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var array
     */
    protected $items = [];

    public function __construct()
    {
        $this->position = 0;
    }

    /**
     * Add invoice item to invoice
     *
     * @param Item $item
     */
    public function  add(Item $item)
    {
        $this->items[] = $item;
    }

    /**
     * Get array of invoice items
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach($this->items as $item)
        {
            $result[] = $item->toArray();
        }

        return $result;
    }

    /**
     *  Get number of elements on list
     */
    public function count()
    {
        return count($this->items);
    }


    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Iterator
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->items[$this->position];
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
        return isset($this->items[$this->position]);
    }
}