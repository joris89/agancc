<?php

namespace App\Repository;

use App\Entity\CartItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CartItem>
 */
class CartItemRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartItem::class);
    }

    /**
     * @param CartItem $cartItem
     * @return CartItem
     */
    public function save(CartItem $cartItem): CartItem
    {
        $em = $this->getEntityManager();
        $em->persist($cartItem);
        $em->flush();

        return $cartItem;
    }

    /**
     * @param CartItem $cartItem
     * @return void
     */
    public function removeCartItem(CartItem $cartItem): void
    {
        $em = $this->getEntityManager();

        $em->remove($cartItem);
        $em->flush();
    }
}
