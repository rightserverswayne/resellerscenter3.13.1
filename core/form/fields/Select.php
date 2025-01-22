<?php
namespace MGModule\ResellersCenter\core\form\fields;
use MGModule\ResellersCenter\core\form\Element;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Select
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Select extends Element
{
    protected $multiple = '';
    
    protected $readonly = '';
    
    protected $options  = array();

    public function __construct($name, $label = '', $description = '', $value = true, $defaultValue = '', $options = array(), $data = array(), $disabled = false, $multiple = false, $readonly = false) 
    {
        $this->name         = $name;
        $this->label        = $label;
        $this->description  = $description;
        $this->value        = $value;
        $this->defaultValue = $defaultValue;
        $this->data         = $data;
        $this->disabled     = $disabled;
        
        $this->multiple = $multiple;       
        $this->readonly = $readonly;       
        $this->options  = $options;       
    }
}
