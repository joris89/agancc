<?php

namespace App\Tests\Unit\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Exception\CartItemNotFoundException;
use App\Exception\CartNotFoundException;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use App\Request\UpdateCartItemRequest;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CartServiceTest extends TestCase
{
    private CartRepository&MockObject $cartRepository;

    private CartItemRepository&MockObject $cartItemRepository;

    private CartService $cartService;

    private EntityManagerInterface&MockObject $entityManager;

    public function setUp(): void
    {
        $this->cartRepository = $this->createMock(CartRepository::class);
        $this->cartItemRepository = $this->createMock(CartItemRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->cartService = new CartService(
            $this->cartRepository,
            $this->cartItemRepository,
            $this->entityManager
        );
    }

    public function testCreateCart(): void
    {
        $this->cartRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Cart::class));

        $result = $this->cartService->createCart();

        $this->assertInstanceOf(Cart::class, $result);
    }

    public function testGetCart(): void
    {
        $cartId = "cart_uuid";
        $cart = new Cart();

        $this->cartRepository
            ->expects($this->once())
            ->method('find')
            ->with($cartId)
            ->willReturn($cart);

        $result = $this->cartService->getCart($cartId);

        $this->assertEquals($cart, $result);
    }

    public function testGetCartReturnsThrowsExceptionWhenNoCartWasFound(): void
    {
        $cartId = "cart_uuid";

        $this->expectException(CartNotFoundException::class);

        $this->cartRepository
            ->expects($this->once())
            ->method('find')
            ->with($cartId)
            ->willReturn(null);

        $this->cartService->getCart($cartId);
    }

    public function testAddItemToCartThrowsExceptionWhenCartNotFound(): void
    {
        $cartId = "cart_uuid";

        $this->entityManager
            ->expects($this->never())
            ->method('getRepository')
            ->with(Product::class);

        $this->cartRepository
            ->expects($this->once())
            ->method('find')
            ->with($cartId)
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cart not found');

        $this->cartService->addItemToCart($cartId, 'product_uuid', 1);
    }

    public function testAddItemToCartThrowsExceptionWhenProductNotFound(): void
    {
        $cartId = "cart_uuid";
        $productId = "product_uuid";
        $cart = new Cart();
        $entityRepository = $this->createMock(EntityRepository::class);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Product::class)
            ->willReturn($entityRepository);

        $entityRepository
            ->expects($this->once())
            ->method('find')
            ->with($productId)
            ->willReturn(null);

        $this->cartRepository
            ->expects($this->once())
            ->method('find')
            ->with($cartId)
            ->willReturn($cart);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Product not found');

        $this->cartService->addItemToCart($cartId, 'product_uuid', 1);
    }

    public function testAddItemToCart(): void
    {
        $cartId = "cart_uuid";
        $productId = "product_uuid";
        $quantity = 1;

        $cart = new Cart();
        $product = new Product();

        $cartItem = new CartItem();
        $cartItem->setProduct($product);
        $cartItem->setQuantity($quantity);
        $cartItem->setCart($cart);

        $this->cartRepository
            ->expects($this->once())
            ->method('find')
            ->with($cartId)
            ->willReturn($cart);

        $entityRepository = $this->createMock(EntityRepository::class);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Product::class)
            ->willReturn($entityRepository);

        $entityRepository
            ->expects($this->once())
            ->method('find')
            ->with($productId)
            ->willReturn($product);

        $this->cartItemRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(CartItem::class));

        $result = $this->cartService->addItemToCart($cartId, $productId, $quantity);

        $this->assertEquals($cartItem, $result);
    }

    public function testRemoveCartItem(): void
    {
        $cartItemId = "cart_item_uuid";
        $cartItem = new CartItem();

        $this->cartItemRepository
            ->expects($this->once())
            ->method('find')
            ->with($cartItemId)
            ->willReturn($cartItem);

        $this->cartItemRepository
            ->expects($this->once())
            ->method('removeCartItem')
            ->with($cartItem);

        $this->cartService->removeCartItem($cartItemId);
    }

    public function testRemoveCartItemThrowsExceptionWhenCartItemNotFound(): void
    {
        $cartItemId = "cart_item_uuid";
        $cartItem = new CartItem();

        $this->cartItemRepository
            ->expects($this->once())
            ->method('find')
            ->with($cartItemId)
            ->willReturn(null);

        $this->cartItemRepository
            ->expects($this->never())
            ->method('removeCartItem')
            ->with($cartItem);

        $this->expectException(CartItemNotFoundException::class);

        $this->cartService->removeCartItem($cartItemId);
    }

    public function testUpdateCartItem(): void
    {
        $cartItem = new CartItem();
        $cartItemId = "cart_item_uuid";
        $quantity = 12;
        $request = new UpdateCartItemRequest($quantity);

        $this->cartItemRepository
            ->expects($this->once())
            ->method('find')
            ->with($cartItemId)
            ->willReturn($cartItem);

        $this->cartItemRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(CartItem::class))
            ->willReturn($cartItem);

        $result = $this->cartService->updateCartItem($cartItemId, $request);
        $this->assertEquals($cartItem, $result);
        $this->assertEquals($quantity, $cartItem->getQuantity());
    }

    public function testUpdateCartItemNotFound(): void
    {
        $cartItemId = "cart_item_uuid";
        $quantity = 12;
        $request = new UpdateCartItemRequest($quantity);

        $this->expectException(CartItemNotFoundException::class);

        $this->cartItemRepository
            ->expects($this->once())
            ->method('find')
            ->with($cartItemId)
            ->willReturn(null);

        $this->cartItemRepository
            ->expects($this->never())
            ->method('save');

        $this->cartService->updateCartItem($cartItemId, $request);
    }
}
