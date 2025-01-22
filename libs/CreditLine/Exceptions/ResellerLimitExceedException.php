<?php

namespace MGModule\ResellersCenter\libs\CreditLine\Exceptions;

class ResellerLimitExceedException extends \Exception
{
    protected const MESSAGE = 'resellerNotEnoughCredits';

    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, $code, $previous);
    }
}