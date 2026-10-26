<?php

namespace App\Exceptions;

use Exception;

class InsufficientCreditsException extends Exception
{
    public function __construct(string $message = 'Insufficient credits')
    {
        parent::__construct($message);
    }
}
