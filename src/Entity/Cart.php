<?php

namespace App\Entity;

use App\Repository\CartRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(["cart", "cartItem"])]
    private ?UuidInterface $id = null;

    /**
     * @var Collection<int, CartItem>
     */
    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: "cart", fetch: "EAGER", orphanRemoval: true)]
    #[Groups(["cart"])]
    private Collection $cartItems;

    #[ORM\Column]
    #[Groups(["cart", "cartItem"])]
    private DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->cartItems = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }
}
