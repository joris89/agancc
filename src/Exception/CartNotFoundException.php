<?php

namespace App\Exception;

class CartNotFoundException extends \Exception
{
    public function __construct(string $message = 'Cart not found', int $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
