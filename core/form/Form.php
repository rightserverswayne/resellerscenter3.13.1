<?php
namespace MGModule\ResellersCenter\core\form;

/**
 * Description of Form
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Form 
{
    protected $configuration = array();
    
    public function add($item)
    {
        if(! $item instanceof Element)
        {
            throw new \Exception("Invalid item type");
        }
        
        if( isset($this->configuration[$item->name]) )
        {
            throw new \Exception("Item with that name already exists");
        }
        
        $this->configuration[$item->name] = $item;
        
        return $this;
    }
    
    public function get($itemName)
    {
        if(! isset($this->configuration[$itemName]) )
        {
            throw new \Exception("Element {$itemName} does not exists");
        }
        
        return $this->configuration[$itemName];
    }
    
    public function delete($itemName)
    {
        if(! isset($this->configuration[$itemName]) )
        {
            throw new \Exception("Element {$itemName} does not exists");
        }
        
        unset($this->configuration[$itemName]);
    }
    
    public function set($itemName, $newValue, $validate = false)
    {
        if(! isset($this->configuration[$itemName]) )
        {
            throw new \Exception("Element {$itemName} does not exists");
        }
        
        $this->configuration[$itemName]->set($newValue, $validate);
    }
    
    public function getConfiguration()
    {
        return $this->configuration;
    }
    
    public function validate()
    {
        $result = array();
        foreach($this->configuration as $field)
        {
            $errors = $field->validate();
            if(!empty($errors))
            {
                $result[] = array("field" => $field->name, "errors" => $errors);
            }
        }
        
        return $result;
    }
    
    public function getHTML($formArrayKey = [])
    {        
        $html = '';
        foreach($this->configuration as $field) 
        {
            if(!empty($formArrayKey)) {
                $nameOnForm = $this->getFieldNameWithArray($formArrayKey);
            }
            
            $html .= $field->parseElement($nameOnForm);
        }

        return $html;
    }
    
    public function getFieldNameWithArray($arrayKeys)
    {
        $result = '';
        foreach($arrayKeys as $index => $key) 
        {
            if($index == 0) {
                $result = $key;
                continue;
            }
            
            $result .= "[$key]";
        }
        
        return $result;
    }
}
