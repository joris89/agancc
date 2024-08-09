<?php

namespace App\Controller;

use App\Exception\CartItemNotFoundException;
use App\Exception\CartNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Request\AddItemRequest;
use App\Request\UpdateCartItemRequest;
use App\Service\CartService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    private CartService $cartService;
    private LoggerInterface $logger;

    public function __construct(CartService $cartService, LoggerInterface $logger)
    {
        $this->cartService = $cartService;
        $this->logger = $logger;
    }

    #[Route('/carts', name: 'carts', methods: ['POST'], format: 'json')]
    public function create(): JsonResponse
    {
        $cart = $this->cartService->createCart();

        return $this->json($cart, JsonResponse::HTTP_CREATED, [], ['groups' => ['cart']]);
    }

    #[Route('/carts/{id}', name: 'carts_view', methods: ['GET'], format: 'json')]
    public function view(string $id): JsonResponse
    {
        try {
            $cart = $this->cartService->getCart($id);

            return $this->json($cart, JsonResponse::HTTP_OK, [], ['groups' => ['cart']]);
        } catch (CartNotFoundException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    #[Route('/carts/{id}/items', name: 'carts_add_item', methods: ['POST'], format: 'json')]
    public function addItem(#[MapRequestPayload] AddItemRequest $request, string $id): JsonResponse
    {
        try {
            $cartItem = $this->cartService->addItemToCart($id, $request->product_id, $request->quantity);

            return $this->json($cartItem, JsonResponse::HTTP_CREATED, [], ['groups' => ['cartItem']]);
        } catch (CartNotFoundException | ProductNotFoundException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    #[Route('/carts/{id}/items/{cartItemId}', name: 'carts_update_item', methods: ['PATCH'], format: 'json')]
    public function updateItem(
        #[MapRequestPayload] UpdateCartItemRequest $request,
        string $id,
        string $cartItemId
    ): JsonResponse {
        try {
            $cartItem = $this->cartService->updateCartItem($cartItemId, $request);

            return $this->json($cartItem, JsonResponse::HTTP_OK, [], ['groups' => ['cartItem']]);
        } catch (CartItemNotFoundException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    #[Route('/carts/{cartId}/items/{cartItemId}', name: 'carts_remove_item', methods: ['DELETE'], format: 'json')]
    public function removeItem(string $cartId, string $cartItemId): JsonResponse
    {
        try {
            $this->cartService->removeCartItem($cartItemId);

            return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
        } catch (CartItemNotFoundException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }
}
