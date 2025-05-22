<?php

namespace App\Entity;

use App\Entity\Interface\HasOwner;
use App\Entity\Trait\HasOwnerTrait;
use App\Repository\ProductionRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
class Production implements HasOwner
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

    #[ORM\Column(length: 50)]
    #[Groups(['production'])]
    private ?string $quantityUnit = null;

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

    /**
     * @var Collection<int, ProductionType>
     */
    #[ORM\ManyToMany(targetEntity: ProductionType::class, mappedBy: 'production')]
    #[Groups(['production'])]
    private Collection $productionTypes;

    public function __construct()
    {
        $this->productionTypes = new ArrayCollection();
    }

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

    public function getQuantityUnit(): ?string
    {
        return $this->quantityUnit;
    }

    public function setQuantityUnit(string $quantityUnit): static
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

    /**
     * @return Collection<int, ProductionType>
     */
    public function getProductionTypes(): Collection
    {
        return $this->productionTypes;
    }

    public function addProductionType(ProductionType $productionType): static
    {
        if (!$this->productionTypes->contains($productionType)) {
            $this->productionTypes->add($productionType);
            $productionType->addProduction($this);
        }

        return $this;
    }

    public function removeProductionType(ProductionType $productionType): static
    {
        if ($this->productionTypes->removeElement($productionType)) {
            $productionType->removeProduction($this);
        }

        return $this;
    }
}
