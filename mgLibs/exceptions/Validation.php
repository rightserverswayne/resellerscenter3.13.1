<?php

namespace MGModule\ResellersCenter\mgLibs\exceptions;
use MGModule\ResellersCenter as main;

/**
 * Use for general module errors
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class validation extends System {
    private $fields = array();
    public function __construct($message,array $fields = array()) {
        $this->fields = $fields;
        parent::__construct($message);
    }
    
    function getFields(){
        return $this->fields;
    }
}
