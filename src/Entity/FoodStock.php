<?php

namespace App\Entity;

use App\Entity\Trait\HasOwnerTrait;
use App\Repository\FoodStockRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: FoodStockRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'foodStocks'),
])]
class FoodStock
{
    use HasOwnerTrait;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['foodStock'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'foodStocks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['foodStock'])]
    private ?Herd $herd = null;

    #[ORM\Column]
    #[Groups(['foodStock'])]
    private ?float $quantity = null;

    #[ORM\Column(length: 50)]
    #[Groups(['foodStock'])]
    private ?string $quantityUnit = null;

    #[ORM\Column]
    #[Groups(['foodStock'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['foodStock'])]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHerd(): ?Herd
    {
        return $this->herd;
    }

    public function setHerd(?Herd $herd): static
    {
        $this->herd = $herd;

        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getQuantityUnit(): ?string
    {
        return $this->quantityUnit;
    }

    public function setQuantityUnit(string $quantityUnit): static
    {
        $this->quantityUnit = $quantityUnit;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
