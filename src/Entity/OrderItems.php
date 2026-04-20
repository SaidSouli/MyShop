<?php

namespace App\Entity;

use App\Repository\OrderItemsRepository;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemsRepository::class)]
class OrderItems
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: 'quantity is required.')]
    #[Assert\Positive(message: 'quantity must be a positive integer.')]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'unit price is required.')]
    #[Assert\PositiveOrZero(message: 'unit price must be a positive number.')]
    private ?string $unitPrice = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $AnOrder = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitPrice(): ?string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): static
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function getAnOrder(): ?Order
    {
        return $this->AnOrder;
    }

    public function setAnOrder(?Order $AnOrder): static
    {
        $this->AnOrder = $AnOrder;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }
    public function getSubtotal(): float
    {
        return (float)$this->unitPrice * $this->quantity;
    }
    public function getproductName(): ?string
    {
        return $this->product?->getName() ?? 'Product no longer available';
    }
}
