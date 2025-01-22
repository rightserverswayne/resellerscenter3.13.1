<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Contents;

/**
 * Description of AbstractContentsIterator
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
abstract class AbstractContentsIterator implements \Iterator
{
    /**
     * Current position in ids array
     *
     * @var type 
     */
    protected $position;

    /**
     * Content ids in content tables
     *
     * @var mixed
     */
    protected $ids;
    
    /**
     * Array with loaded objects
     *
     * @var mixed
     */
    protected $contents;

    public function rewind() 
    {
        //foreach starts from rewind - we have to load objects here if not loaded before
        $this->contents ?: $this->load();
        
        $this->position = 0;
    }

    public function current() 
    {
        $id = $this->ids[$this->position];
        return $this->{$id};
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
        $id = $this->ids[$this->position];
        return $this->contents[$id];
    }
    
    abstract protected function load();
}
