<?php

namespace App\Exception;

class ProductNotFoundException extends \Exception
{
    public function __construct(string $message = 'Product not found', int $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
