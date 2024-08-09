<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Cart;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    public function testConstruct(): void
    {
        $cart = new Cart();

        $this->assertInstanceOf(\DateTime::class, $cart->getCreatedAt());
        $this->assertInstanceOf(ArrayCollection::class, $cart->getCartItems());
    }

    public function testGettersAndSetters(): void
    {
        $createdAt = new \DateTime();
        $cart = new Cart();
        $cart->setCreatedAt($createdAt);

        $this->assertNull($cart->getId());
        $this->assertEquals($cart->getCreatedAt(), $createdAt);
    }
}
