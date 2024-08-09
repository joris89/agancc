<?php

namespace App\Exception;

class CartItemNotFoundException extends \Exception
{
    public function __construct(string $message = 'CartItem not found', int $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
