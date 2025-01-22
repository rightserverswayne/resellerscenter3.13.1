<?php

namespace MGModule\ResellersCenter\Core\Traits;

/**
 * Description of PropertiesExtension
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */

trait HasProperties
{
    /**
     * Get create and return object of class property
     *
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        //get all available properties from the resource object
        $properties = get_class_vars(get_class($this));
        $disabled   = $this->getDisabledProperties();

        //Check if requested name is on the list
        if (array_key_exists($name, $properties) !== false && !in_array($name, $disabled))
        {
            //If the name is on the list and it is not disabled, lets load an object
            $result         = $this->{$name} ?: $this->getPropertyObject($name);
            $this->{$name}  = $this->{$name} ?: $result;
        }
        else
        {
            //Lets check if this object has model
            if($this->hasModalTrait())
            {
                $this->model ?: $this->load();
                $result = $this->model->{$name};
            }
            else
            {
                $result = $this->{$name};
            }
        }

        return $result;
    }

    /**
     * Get property object using its model
     *
     * @param $name
     * @return mixed
     * @throws \ReflectionException
     */
    private function getPropertyObject($name)
    {
        //Get overridden properties
        $overrides = $this->getOverriddenPropertiesClasses();

        //Check if the property name is on the list of overridden classes
        if(array_key_exists($name, $overrides))
        {
            $class  = $overrides[$name]["class"];
            $model  = $overrides[$name]["model"];
            $parent = $overrides[$name]["parent"];

            $result = $this->hasModalTrait($class) ? new $class($model, $parent ?: $this) : new $class($parent ?: $this);
        }
        else
        {
            $class  = get_class($this);
            $result = $this->getPropertyRecurring($name, $class);
        }

        return $result;
    }

    /**
     * Get property object
     *
     * @param $name
     * @param $class
     * @return mixed
     * @throws \ReflectionException
     */
    private function getPropertyRecurring($name, $class)
    {
        $namespace = (new \ReflectionClass($class))->getNamespaceName();
        $classname = $namespace.'\\'.ucfirst($name);

        if(class_exists($classname))
        {
            $result = $this->hasModalTrait($classname) ? new $classname($this->model->{$name}, $this) : new $classname($this);
        }
        elseif(get_parent_class($class))
        {
            $class  = get_parent_class($class);
            $result = $this->getPropertyRecurring($name, $class);
        }

        return $result;
    }

    /**
     * Check if class is using HasModal trait
     *
     * @param null $classname
     * @return bool
     */
    private function hasModalTrait($classname = null)
    {
        $result = false;
        $class  = $classname ?: get_class($this);
        $traits = $this->getTraitsRecurring($class);

        if(in_array(HasModel::class, $traits))
        {
            $result = true;
        }

        return $result;
    }

    /**
     * Get traits from class and its parents
     *
     * @param $classname
     * @return array
     */
    private function getTraitsRecurring($classname)
    {
        $result = class_uses($classname) ?: [];
        $class  = get_parent_class($classname);

        if($class)
        {
            $traits = $this->getTraitsRecurring($class);
            $result = array_merge($result, $traits);
        }

        return $result;
    }

    /**
     * Override property classes
     *
     * @return array
     */
    protected function getOverriddenPropertiesClasses()
    {
        return [];
    }

    /**
     * Get disabled properties (properties that should return basic value)
     *
     * @return array
     */
    protected function getDisabledProperties()
    {
        return ["id", "model"];
    }
}