<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class CartItemTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $cart = new Cart();
        $product = new Product();
        $quantity = 1;

        $cartItem = new CartItem();
        $cartItem->setProduct($product);
        $cartItem->setQuantity($quantity);
        $cartItem->setCart($cart);

        $this->assertEquals($product, $cartItem->getProduct());
        $this->assertEquals($quantity, $cartItem->getQuantity());
        $this->assertEquals($cart, $cartItem->getCart());
        $this->assertNull($cartItem->getId());
    }
}
