<?php

namespace App\Tests\Integration\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Exception\CartItemNotFoundException;
use App\Exception\CartNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Repository\CartRepository;
use App\Request\UpdateCartItemRequest;
use App\Service\CartService;
use App\Tests\Integration\IntegrationTestCase;
use Ramsey\Uuid\UuidInterface;

class CartServiceIntegrationTest extends IntegrationTestCase
{
    private CartService $cartService;

    public function setUp(): void
    {
        parent::setUp();

        /** @var CartService $cartService */
        $cartService = $this->getContainer()->get(CartService::class);
        $this->cartService = $cartService;
    }

    public function testCreateCart(): void
    {
        $cart = $this->cartService->createCart();

        /** @var CartRepository $cartRepository */
        $cartRepository = $this->getContainer()->get(CartRepository::class);

        $this->assertInstanceOf(UuidInterface::class, $cart->getId());
        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertInstanceOf(\DateTime::class, $cart->getCreatedAt());
        $this->assertCount(1, $cartRepository->findAll());
    }

    public function testGetCart(): void
    {
        $cart = $this->cartService->createCart();

        $result = $this->cartService->getCart($cart->getId());

        $this->assertEquals($cart, $result);
    }

    public function testGetCartNoCartFound(): void
    {
        $this->expectException(CartNotFoundException::class);
        $this->cartService->getCart('6891389f-95a1-4585-a56f-ccd9fa829124');
    }

    public function testAddItemToCartThrowsExceptionWhenNoProductFound(): void
    {
        $cart = $this->cartService->createCart();

        $this->expectException(ProductNotFoundException::class);
        $this->cartService->addItemToCart($cart->getId(), '6891389f-95a1-4585-a56f-ccd9fa829124', 10);
    }

    public function testAddItemToCartThrowsExceptionWhenNoCartFound(): void
    {
        $this->expectException(CartNotFoundException::class);
        $this->cartService->addItemToCart(
            '6891389f-95a1-4585-a56f-ccd9fa829124',
            '6891389f-95a1-4585-a56f-ccd9fa829124',
            10
        );
    }

    public function testAddItemToCart(): void
    {
        $cart = $this->cartService->createCart();
        $quantity = 1;
        $entityManager = $this->getEntityManager();
        $product = new Product();
        $product->setName('Product A')
            ->setPrice(100.00);
        $entityManager->persist($product);
        $entityManager->flush();

        $result = $this->cartService->addItemToCart($cart->getId(), $product->getId(), $quantity);

        $this->assertInstanceOf(CartItem::class, $result);
        $this->assertEquals($product->getId(), $result->getProduct()->getId());
        $this->assertEquals($cart->getId(), $result->getCart()->getId());
        $this->assertEquals($quantity, $result->getQuantity());
    }

    public function testRemoveCartItem(): void
    {
        $cart = $this->cartService->createCart();
        $quantity = 1;
        $entityManager = $this->getEntityManager();
        $product = new Product();
        $product->setName('Product A')
            ->setPrice(100.00);
        $entityManager->persist($product);
        $entityManager->flush();

        $cartItem = $this->cartService->addItemToCart($cart->getId(), $product->getId(), $quantity);

        $result = $this->cartService->removeCartItem($cartItem->getId());

        $this->assertTrue($result);
    }

    public function testRemoveCartItemNoCartItemFound(): void
    {
        $this->expectException(CartItemNotFoundException::class);
        $this->cartService->removeCartItem('6891389f-95a1-4585-a56f-ccd9fa829124');
    }

    public function testUpdateCartItem(): void
    {
        $cart = $this->cartService->createCart();
        $quantity = 1;
        $entityManager = $this->getEntityManager();
        $product = new Product();
        $product->setName('Product A')
            ->setPrice(100.00);
        $entityManager->persist($product);
        $entityManager->flush();

        $cartItem = $this->cartService->addItemToCart($cart->getId(), $product->getId(), $quantity);
        $request = new UpdateCartItemRequest(12);

        $result = $this->cartService->updateCartItem($cartItem->getId(), $request);

        $this->assertInstanceOf(CartItem::class, $result);
        $this->assertEquals(12, $cartItem->getQuantity());
    }

    public function testUpdateCartItemNotFound(): void
    {
        $this->expectException(CartItemNotFoundException::class);
        $request = new UpdateCartItemRequest(12);
        $this->cartService->updateCartItem('6891389f-95a1-4585-a56f-ccd9fa829124', $request);
    }
}
