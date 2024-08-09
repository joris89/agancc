<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Exception\CartItemNotFoundException;
use App\Exception\CartNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Request\UpdateCartItemRequest;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use InvalidArgumentException;

class CartService
{
    protected CartRepository $cartRepository;

    protected CartItemRepository $cartItemRepository;

    private EntityManagerInterface $entityManager;

    /**
     * @param CartRepository $cartRepository
     * @param CartItemRepository $cartItemRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        CartRepository $cartRepository,
        CartItemRepository $cartItemRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->cartRepository = $cartRepository;
        $this->cartItemRepository = $cartItemRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @return Cart
     */
    public function createCart(): Cart
    {
        $cart = new Cart();

        return $this->cartRepository->save($cart);
    }

    /**
     * @param string $id
     * @return Cart
     * @throws CartNotFoundException
     */
    public function getCart(string $id): Cart
    {
        return $this->getCartOrThrow($id);
    }

    /**
     * @param string $cartId
     * @param string $productId
     * @param int $quantity
     * @return CartItem
     * @throws CartNotFoundException
     * @throws ProductNotFoundException
     */
    public function addItemToCart(string $cartId, string $productId, int $quantity): CartItem
    {
        $cart = $this->getCartOrThrow($cartId);
        $product = $this->entityManager->getRepository(Product::class)->find($productId);

        if (!$product) {
            throw new ProductNotFoundException();
        }

        $cartItem = new CartItem();
        $cartItem->setCart($cart);
        $cartItem->setproduct($product);
        $cartItem->setQuantity($quantity);

        $this->cartItemRepository->save($cartItem);

        return $cartItem;
    }

    /**
     * @param string $cartItemId
     * @return bool
     * @throws CartItemNotFoundException
     */
    public function removeCartItem(string $cartItemId): bool
    {
        $cartItem = $this->getCartItemOrThrow($cartItemId);
        $this->cartItemRepository->removeCartItem($cartItem);

        return true;
    }

    /**
     * @param string $cartItemId
     * @param UpdateCartItemRequest $request
     * @return CartItem
     * @throws CartItemNotFoundException
     */
    public function updateCartItem(string $cartItemId, UpdateCartItemRequest $request): CartItem
    {
        $cartItem = $this->getCartItemOrThrow($cartItemId);

        if ($request->quantity) {
            $cartItem->setQuantity($request->quantity);
        }

        return $this->cartItemRepository->save($cartItem);
    }

    /**
     * @param string $cartId
     * @return Cart
     * @throws CartNotFoundException
     */
    private function getCartOrThrow(string $cartId): Cart
    {
        $cart = $this->cartRepository->find($cartId);
        if (!$cart) {
            throw new CartNotFoundException('Cart not found');
        }
        return $cart;
    }

    /**
     * @param string $cartItemId
     * @return CartItem
     * @throws CartItemNotFoundException
     */
    private function getCartItemOrThrow(string $cartItemId): CartItem
    {
        $cartItem = $this->cartItemRepository->find($cartItemId);
        if (!$cartItem) {
            throw new CartItemNotFoundException('CartItem not found');
        }
        return $cartItem;
    }
}
