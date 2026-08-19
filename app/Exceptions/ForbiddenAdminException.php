<?php

namespace App\Exceptions;

use Exception;

class ForbiddenAdminException extends Exception
{
    public function __construct(string $message = 'Forbidden: Administrator privileges required.')
    {
        parent::__construct($message);
    }
}
