<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers\Relations;
use MGModule\ResellersCenter\core\EventManager;

/**
 * Description of AbstractRelations
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgaren.com>
 */
abstract class AbstractRelations
{
    /**
     * Reseller Object
     *
     * @var \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller
     */
    protected $reseller;
    
    /**
     * Define type ambiguous type.
     * If true then relation can belong to many Resellers
     *
     * @var boolean
     */
    protected $ambiguous;
    
    /**
     * Assign reseller
     * 
     * @param \MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller
     */
    public function __construct(\MGModule\ResellersCenter\Core\Resources\Resellers\Reseller $reseller)
    {
        $this->reseller = $reseller;
    }
    
    /**
     * If method is not found in current class use repo methods
     * 
     * @param type $name
     * @param type $arguments
     */
    public function __call($name, $arguments)
    {
        $repo = $this->getRepo();
        $result = $repo->{$name}($arguments);
        
        return $result;
    }
    
    /**
     * Create new relation
     * 
     * @param integer $relid
     * @throws \Exception
     */
    public function assign($relid)
    {
        $repo = $this->getRepo();
        $type = trim($this->getSelfType(), "s");

        $relation = $repo->getByRelId($relid, $type);
        if($relation->exists && !$this->ambiguous)
        {
            throw new \Exception("Unable to assign {$type} #{$relid} to reseller #{$this->reseller->id}. Already assigned to different Reseller");
        }
        
        $repo->createNew($this->reseller->id, $relid, trim($type, "s"));
        EventManager::call("newServiceRelation", trim($type, "s"), $relid, $this->reseller->id);
    }
    
    /**
     * Delete relation
     * 
     * @param integer $relid
     * @throws \Exception
     */
    public function unassign($relid)
    {
        $repo = $this->getRepo();
        $type = trim($this->getSelfType(), "s");
        
        $relation = $repo->getByRelId($relid, $type);
        if(!$relation->exists)
        {
            $class = get_class($this);
            throw new \Exception("Unable to unassign {$class} #{$relid} form reseller #{$this->reseller->id}. Object is not assigned to specified reseller.");
        }
        
        $relation->delete();
    }
    
    /**
     * Find relation by relid
     * 
     * @param type $relid
     * @return Model
     */
    public function find($relid)
    {
        $repo = $this->getRepo();
        $type = trim($this->getSelfType(), "s");
        
        $result = $repo->getByRelId($relid, $type, $this->reseller->id);
        return $result->{$type};
    }

    /**
     * Get loaded relations type
     *
     * @return string
     * @throws \ReflectionException
     */
    protected function getSelfType()
    {
        $classname = (new \ReflectionClass($this))->getShortName();
        return strtolower($classname);
    }
    
    abstract protected function getRepo();
}
