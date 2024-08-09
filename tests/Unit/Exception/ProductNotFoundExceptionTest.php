<?php

namespace App\Tests\Unit\Exception;

use App\Exception\ProductNotFoundException;
use PHPUnit\Framework\TestCase;

class ProductNotFoundExceptionTest extends TestCase
{
    public function testExceptionMessage(): void
    {
        $exception = new ProductNotFoundException();
        $this->assertEquals('Product not found', $exception->getMessage());
    }

    public function testCustomExceptionMessage(): void
    {
        $message = 'Custom message';
        $exception = new ProductNotFoundException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionCode(): void
    {
        $code = 404;
        $exception = new ProductNotFoundException('Product not found', $code);
        $this->assertEquals($code, $exception->getCode());
    }
}
