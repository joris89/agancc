<?php

namespace App\Tests\Unit\Exception;

use App\Exception\CartItemNotFoundException;
use PHPUnit\Framework\TestCase;

class CartItemNotFoundExceptionTest extends TestCase
{
    public function testExceptionMessage(): void
    {
        $exception = new CartItemNotFoundException();
        $this->assertEquals('CartItem not found', $exception->getMessage());
    }

    public function testCustomExceptionMessage(): void
    {
        $message = 'Custom message';
        $exception = new CartItemNotFoundException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionCode(): void
    {
        $code = 404;
        $exception = new CartItemNotFoundException('CartItem not found', $code);
        $this->assertEquals($code, $exception->getCode());
    }
}
