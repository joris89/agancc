<?php

namespace App\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use App\Exception\CartNotFoundException;

class CartNotFoundExceptionTest extends TestCase
{
    public function testExceptionMessage(): void
    {
        $exception = new CartNotFoundException();
        $this->assertEquals('Cart not found', $exception->getMessage());
    }

    public function testCustomExceptionMessage(): void
    {
        $message = 'Custom message';
        $exception = new CartNotFoundException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionCode(): void
    {
        $code = 404;
        $exception = new CartNotFoundException('Cart not found', $code);
        $this->assertEquals($code, $exception->getCode());
    }
}
