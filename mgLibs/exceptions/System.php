<?php

namespace MGModule\ResellersCenter\mgLibs\exceptions;
use MGModule\ResellersCenter as main;

/**
 * Use for general module errors
 *
 * @author Michal Czech <michael@modulesgarden.com>
 */
class System extends Base {
    public function __construct($message, $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
