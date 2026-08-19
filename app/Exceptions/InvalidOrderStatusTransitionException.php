<?php

namespace App\Exceptions;

use Exception;

class InvalidOrderStatusTransitionException extends Exception
{
    public function __construct(string $fromStatus, string $toStatus)
    {
        parent::__construct("Cannot transition order status from '{$fromStatus}' to '{$toStatus}'. This state change is not allowed by the order lifecycle rules.");
    }
}
