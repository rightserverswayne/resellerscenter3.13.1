<?php

namespace MGModule\ResellersCenter\libs\CreditLine\Exceptions;

class AddCreditException extends \Exception
{
    protected const MESSAGE_PREFIX = "Add credit to Credit Line failed. ";

    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        $message = self::MESSAGE_PREFIX.$message;
        parent::__construct($message, $code, $previous);
    }
}