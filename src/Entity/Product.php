<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity()]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(["product", "cart", "cartItem"])]
    private ?UuidInterface $id = null;

    #[ORM\Column]
    #[Groups(["product", "cart", "cartItem"])]
    private string $name;

    #[ORM\Column]
    #[Groups(["product", "cart", "cartItem"])]
    private float $price;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}
