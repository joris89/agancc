<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $productData = [
            'name' => 'ProductTest 1',
            'price' => 100.99,
        ];

        $product = new Product();
        $product->setName($productData['name']);
        $product->setPrice($productData['price']);

        $this->assertNull($product->getId());
        $this->assertEquals($product->getName(), $productData['name']);
        $this->assertEquals($product->getPrice(), $productData['price']);
    }
}
