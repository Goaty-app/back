<?php

namespace App\Entity;

use App\Contract\HerdAwareInterface;
use App\Entity\Contract\OwnableInterface;
use App\Entity\Trait\OwnableEntityTrait;
use App\Entity\Trait\TimestampableTrait;
use App\Enum\QuantityUnit;
use App\Repository\ProductionRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ProductionRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'productions'),
])]
class Production implements OwnableInterface, HerdAwareInterface
{
    use OwnableEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['production'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['production'])]
    private ?Herd $herd = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['production'])]
    #[Assert\NotBlank()]
    #[Assert\DateTime(format: 'Y-m-d H:i:s', message: 'assert.datetime')]
    private ?DateTimeInterface $productionDate = null;

    #[ORM\Column]
    #[Groups(['production'])]
    #[Assert\NotBlank()]
    private ?float $quantity = null;

    #[ORM\Column(enumType: QuantityUnit::class, nullable: false)]
    #[Groups(['production'])]
    #[Assert\NotBlank()]
    #[Assert\Choice(callback: [QuantityUnit::class, 'enumValues'], message: 'assert.choice')]
    private ?QuantityUnit $quantityUnit = null;

    #[ORM\ManyToOne(inversedBy: 'production')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['production'])]
    #[Assert\NotBlank()]
    private ?ProductionType $productionType = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['production'])]
    #[Assert\DateTime(format: 'Y-m-d H:i:s', message: 'assert.datetime')]
    private ?DateTimeInterface $expirationDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['production'])]
    #[Assert\Length(
        max: 255,
    )]
    private ?string $notes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductionDate(): ?DateTimeInterface
    {
        return $this->productionDate;
    }

    public function setProductionDate(DateTimeInterface $productionDate): static
    {
        $this->productionDate = $productionDate;

        return $this;
    }

    public function getExpirationDate(): ?DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?DateTimeInterface $expirationDate): static
    {
        $this->expirationDate = $expirationDate;

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
