<?php
namespace MGModule\ResellersCenter\core\form\fields;
use MGModule\ResellersCenter\core\form\Element;

/**
 * Description of Textarea
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Textarea extends Element
{
    protected $placeholder = '';

    public function __construct($name, $label, $description = '', $value = '', $placeholder = '', $data = array(), $disabled = false, $validation = array()) 
    {
        $this->name         = $name;
        $this->label        = $label;
        $this->description  = $description;
        $this->value        = $value;
        $this->data         = $data;
        $this->disabled     = $disabled;
        $this->placeholder  = $placeholder;       
        $this->validation   = $validation;
    }
}
