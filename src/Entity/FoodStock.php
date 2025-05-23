<?php

namespace App\Entity;

use App\Enum\QuantityUnit;
use App\Interface\HasOwner;
use App\Repository\FoodStockRepository;
use App\Trait\HasOwnerTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: FoodStockRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'foodStocks'),
])]
class FoodStock implements HasOwner
{
    use HasOwnerTrait;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['foodStock', 'foodStockHistory'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'foodStocks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['foodStock'])]
    private ?Herd $herd = null;

    #[ORM\Column]
    #[Groups(['foodStock', 'foodStockHistory'])]
    private ?float $quantity = null;

    #[ORM\Column(enumType: QuantityUnit::class, nullable: false)]
    #[Groups(['foodStock', 'foodStockHistory'])]
    private ?QuantityUnit $quantityUnit = null;

    #[ORM\Column]
    #[Groups(['foodStock', 'foodStockHistory'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['foodStock', 'foodStockHistory'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'foodStocks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['foodStock'])]
    private ?FoodStockType $foodStockType = null;

    /**
     * @var Collection<int, FoodStockHistory>
     */
    #[ORM\OneToMany(targetEntity: FoodStockHistory::class, mappedBy: 'foodStock', orphanRemoval: true)]
    private Collection $foodStockHistories;

    public function __construct()
    {
        $this->foodStockHistories = new ArrayCollection();
    }

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

    public function getQuantityUnit(): ?QuantityUnit
    {
        return $this->quantityUnit;
    }

    public function setQuantityUnit(QuantityUnit $quantityUnit): static
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

    public function getFoodStockType(): ?FoodStockType
    {
        return $this->foodStockType;
    }

    public function setFoodStockType(?FoodStockType $foodStockType): static
    {
        $this->foodStockType = $foodStockType;

        return $this;
    }

    /**
     * @return Collection<int, FoodStockHistory>
     */
    public function getFoodStockHistories(): Collection
    {
        return $this->foodStockHistories;
    }

    public function addFoodStockHistory(FoodStockHistory $foodStockHistory): static
    {
        if (!$this->foodStockHistories->contains($foodStockHistory)) {
            $this->foodStockHistories->add($foodStockHistory);
            $foodStockHistory->setFoodStock($this);
        }

        return $this;
    }

    public function removeFoodStockHistory(FoodStockHistory $foodStockHistory): static
    {
        if ($this->foodStockHistories->removeElement($foodStockHistory)) {
            // set the owning side to null (unless already changed)
            if ($foodStockHistory->getFoodStock() === $this) {
                $foodStockHistory->setFoodStock(null);
            }
        }

        return $this;
    }
}
