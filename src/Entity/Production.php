<?php

namespace App\Entity;

use App\Enum\QuantityUnit;
use App\Interface\HasHerd;
use App\Interface\HasOwner;
use App\Repository\ProductionRepository;
use App\Trait\HasOwnerTrait;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ProductionRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'productions'),
])]
class Production implements HasOwner, HasHerd
{
    use HasOwnerTrait;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['production'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['production'])]
    private ?DateTimeInterface $production_date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['production'])]
    private ?DateTimeInterface $expiration_date = null;

    #[ORM\Column]
    #[Groups(['production'])]
    private ?float $quantity = null;

    #[ORM\Column(enumType: QuantityUnit::class, nullable: false)]
    #[Groups(['production'])]
    private ?QuantityUnit $quantityUnit = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['production'])]
    private ?string $notes = null;

    #[ORM\Column]
    #[Groups(['production'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'productions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['production'])]
    private ?Herd $herd = null;

    #[ORM\ManyToOne(inversedBy: 'production')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['production'])]
    private ?ProductionType $productionType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductionDate(): ?DateTimeInterface
    {
        return $this->production_date;
    }

    public function setProductionDate(DateTimeInterface $production_date): static
    {
        $this->production_date = $production_date;

        return $this;
    }

    public function getExpirationDate(): ?DateTimeInterface
    {
        return $this->expiration_date;
    }

    public function setExpirationDate(?DateTimeInterface $expiration_date): static
    {
        $this->expiration_date = $expiration_date;

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

    public function getQuantityUnit(): ?QuantityUnit
    {
        return $this->quantityUnit;
    }

    public function setQuantityUnit(QuantityUnit $quantityUnit): static
    {
        $this->quantityUnit = $quantityUnit;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

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

    public function getHerd(): ?Herd
    {
        return $this->herd;
    }

    public function setHerd(?Herd $herd): static
    {
        $this->herd = $herd;

        return $this;
    }

    public function getProductionType(): ?ProductionType
    {
        return $this->productionType;
    }

    public function setProductionType(?ProductionType $productionType): static
    {
        $this->productionType = $productionType;

        return $this;
    }
}
