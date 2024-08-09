<?php

namespace App\Entity;

use App\Repository\CartItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CartItemRepository::class)]
class CartItem
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(["cart", "cartItem"])]
    private ?UuidInterface $id = null;

    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy:"cartItems")]
    #[Groups(["cartItem"])]
    private Cart $cart;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[Groups(["cart", "cartItem"])]
    private Product $product;

    #[ORM\Column]
    #[Groups(["cart", "cartItem"])]
    private int $quantity = 0;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function setproduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getproduct(): Product
    {
        return $this->product;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setCart(Cart $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }
}
