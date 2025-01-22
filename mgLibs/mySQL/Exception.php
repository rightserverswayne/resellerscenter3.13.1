<?php

namespace MGModule\ResellersCenter\mgLibs\MySQL;
use MGModule\ResellersCenter as main;

/**
 * MySQL Exception
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class Exception extends main\mgLibs\exceptions\System {
    private $_query;
    public function __construct($message, $query, $code = 0, $previous = null) {
        $this->_query = $query;
        $code = (int) $code;
        parent::__construct($message, $code, $previous);
    }
}
