<?php

namespace App\Tests\Unit\Controller;

use App\Controller\CartController;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Exception\CartItemNotFoundException;
use App\Exception\CartNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Request\AddItemRequest;
use App\Request\UpdateCartItemRequest;
use App\Service\CartService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

class CartControllerTest extends KernelTestCase
{
    private CartService&MockObject $cartService;

    private CartController $controller;

    private LoggerInterface&MockObject $logger;

    public function setUp(): void
    {
        $this->cartService = $this->createMock(CartService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->controller = new CartController($this->cartService, $this->logger);
        $this->controller->setContainer(self::getContainer());
    }

    public function testCreateController(): void
    {
        $createdAt = new \DateTime();
        $createdAtFormatted = $createdAt->format('Y-m-d\TH:i:sP');
        $expectedJsonResponseContent = '{"id":null,"cartItems":[],"createdAt":"' . $createdAtFormatted . '"}';
        $cart = new Cart();
        $cart->setCreatedAt($createdAt);
        $this->cartService
            ->expects($this->once())
            ->method('createCart')
            ->willReturn($cart);

        $result = $this->controller->create();

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(JsonResponse::HTTP_CREATED, $result->getStatusCode());
        $this->assertEquals($expectedJsonResponseContent, $result->getContent());
    }

    public function testView(): void
    {
        $cartId = 'cart_uuid';
        $createdAt = new \DateTime();
        $createdAtFormatted = $createdAt->format('Y-m-d\TH:i:sP');
        $expectedJsonResponseContent = '{"id":null,"cartItems":[],"createdAt":"' . $createdAtFormatted . '"}';
        $cart = new Cart();
        $cart->setCreatedAt($createdAt);

        $this->cartService->expects($this->once())
            ->method('getCart')
            ->with($cartId)
            ->willReturn($cart);

        $result = $this->controller->view($cartId);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(JsonResponse::HTTP_OK, $result->getStatusCode());
        $this->assertEquals($expectedJsonResponseContent, $result->getContent());
    }

    public function testViewNoCartFound(): void
    {
        $cartId = 'cart_uuid';
        $exception = new CartNotFoundException();
        $this->cartService
            ->expects($this->once())
            ->method('getCart')
            ->with($cartId)
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($exception->getMessage(), ['exception' => $exception]);

        $result = $this->controller->view($cartId);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $result->getStatusCode());
        $this->assertEquals(json_encode(['error' => $exception->getMessage()]), $result->getContent());
    }

    public function testAddItem(): void
    {
        $productId = 'product_uuid';
        $quantity = 2;
        $cartId = 'cart_uuid';

        $request = new AddItemRequest($productId, $quantity);
        $this->cartService->expects($this->once())
            ->method('addItemToCart')
            ->with($cartId, $productId, $quantity);

        $result = $this->controller->addItem($request, $cartId);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(JsonResponse::HTTP_CREATED, $result->getStatusCode());
    }

    public function testAddItemNoCartFound(): void
    {
        $productId = 'product_uuid';
        $quantity = 2;
        $cartId = 'cart_uuid';

        $exception = new CartNotFoundException();
        $request = new AddItemRequest($productId, $quantity);

        $this->cartService
            ->expects($this->once())
            ->method('addItemToCart')
            ->with($cartId, $productId, $quantity)
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($exception->getMessage(), ['exception' => $exception]);

        $result = $this->controller->addItem($request, $cartId);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $result->getStatusCode());
        $this->assertEquals(json_encode(['error' => $exception->getMessage()]), $result->getContent());
    }

    public function testAddItemNoProductFound(): void
    {
        $productId = 'product_uuid';
        $quantity = 2;
        $cartId = 'cart_uuid';

        $exception = new ProductNotFoundException();
        $request = new AddItemRequest($productId, $quantity);

        $this->cartService
            ->expects($this->once())
            ->method('addItemToCart')
            ->with($cartId, $productId, $quantity)
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($exception->getMessage(), ['exception' => $exception]);

        $result = $this->controller->addItem($request, $cartId);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $result->getStatusCode());
        $this->assertEquals(json_encode(['error' => $exception->getMessage()]), $result->getContent());
    }

    public function testRemoveItem(): void
    {
        $cartItemId = 'cartitem_uuid';
        $cartId = 'cart_uuid';

        $this->cartService->expects($this->once())
            ->method('removeCartItem')
            ->with($cartItemId);

        $result = $this->controller->removeItem($cartId, $cartItemId);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(JsonResponse::HTTP_NO_CONTENT, $result->getStatusCode());
    }

    public function testRemoveItemCartItemNotFound(): void
    {
        $cartItemId = 'cartitem_uuid';
        $cartId = 'cart_uuid';
        $exception = new CartItemNotFoundException();

        $this->cartService->expects($this->once())
            ->method('removeCartItem')
            ->with($cartItemId)
            ->willThrowException($exception);

        $result = $this->controller->removeItem($cartId, $cartItemId);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $result->getStatusCode());
        $this->assertEquals(json_encode(['error' => $exception->getMessage()]), $result->getContent());
    }

    public function testUpdateItem(): void
    {
        $quantity = 12;
        $cartItemId = 'cartitem_uuid';
        $request = new UpdateCartItemRequest($quantity);
        $cartItem = new CartItem();

        $this->cartService
            ->expects($this->once())
            ->method('updateCartItem')
            ->with($cartItemId, $request)
            ->willReturn($cartItem);

        $result = $this->controller->updateItem($request, 'cart_uuid', $cartItemId);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(JsonResponse::HTTP_OK, $result->getStatusCode());
    }

    public function testUpdateItemNoCartItemFound(): void
    {
        $quantity = 12;
        $cartItemId = 'cartitem_uuid';
        $request = new UpdateCartItemRequest($quantity);
        $exception = new CartItemNotFoundException();

        $this->cartService
            ->expects($this->once())
            ->method('updateCartItem')
            ->with($cartItemId, $request)
            ->willThrowException($exception);

        $result = $this->controller->updateItem($request, 'cart_uuid', $cartItemId);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $result->getStatusCode());
        $this->assertEquals(json_encode(['error' => $exception->getMessage()]), $result->getContent());
    }
}
