<?php

namespace MGModule\ResellersCenter\libs\CreditLine\Exceptions;

class ClientLimitExceedException extends \Exception
{
    protected const MESSAGE = 'clientNotEnoughCredits';

    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, $code, $previous);
    }
}