<?php

namespace MGModule\ResellersCenter\mgLibs\exceptions\factories;

use MGModule\ResellersCenter\Core\Helpers\Whmcs;
use MGModule\ResellersCenter\mgLibs\exceptions\Base;

class IncorrectClassFoundException extends Base {
    const DEFAULT_MESSAGE = 'incorrectClassFoundException';

    public function __construct($message = null, $code = 0, $previous = null) {
        $message = $message ?? self::DEFAULT_MESSAGE;
        parent::__construct($message, $code, $previous);
    }
}