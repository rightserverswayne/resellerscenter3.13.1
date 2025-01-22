<?php
namespace MGModule\ResellersCenter\core\form;
use MGModule\ResellersCenter\mgLibs\Smarty;

/**
 * Description of Field
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Element 
{
    /**
     * Attribute name value
     * 
     * @var type 
     */
    protected $name = '';
    
    /**
     * Label text
     * 
     * @var type 
     */
    protected $label = '';
    
    /**
     * Description below input
     * 
     * @var type 
     */
    protected $description = '';
    
    /**
     * Attribute value content
     * 
     * @var type 
     */
    protected $value = '';
    
    /**
     * Data attributes.
     * array("foo" => "bar") ↔ data-foo='bar'
     *
     * @var type 
     */
    protected $data = array();
    
    /**
     * Disabled attribute 
     * 
     * @var type 
     */
    protected $disabled = false;
    
    /**
     * Array of styles
     * 
     * @var type 
     */
    protected $style = array();
    
    /**
     * Validation functions
     * 
     * @var type 
     */
    protected $validation = array();
    
    public function __construct($name, $label, $description = '', $value = '', $data = array(), $disabled = false, $validation = array()) 
    {
        $this->name         = $name;
        $this->label        = $label;
        $this->description  = $description;
        $this->value        = $value;
        $this->data         = $data;
        $this->disabled     = $disabled;
        $this->validation   = $validation;
    }
    
    public function __get($optionName)
    {
        if(property_exists($this, $optionName))
        {
            return $this->{$optionName};
        }
    }
    
    public function __toString()
    {
        return $this->value;
    }
    
    public function set($newValue, $validate = false)
    {
        if($validate) {
            $this->validate();
        }
        
        $this->value = $newValue;
    }
            
    public function addStyle($style, $value)
    {
        $this->style[$style] = $value;
        
        return $this;
    }
    
    public function setValidators($rules)
    {
        $this->validation = $rules;
    }
    
    public function validate()
    {
        $result = array();
        if($this->validation)
        {
            $validator = new Validator($this->validation);
            $result = $validator->validate($this->value);
        }
        
        return $result;
    }
    
    public function parseElement($nameOnForm)
    {
        //Set temp name
        if($nameOnForm) 
        {
            $original = $this->name;
            $this->name = $nameOnForm."[$this->name]";
        }
        
        $template = join('', array_slice(explode('\\', get_class($this)), -1));
        $objVars = get_object_vars($this);
        
        $dir = __DIR__ . DS . 'fields' . DS . "templates";
        $result = Smarty::I()->view(strtolower($template), $objVars, $dir);
        
        //Restore name
        if($original) {
            $this->name = $original;
        }
        
        return $result;
    }
}
