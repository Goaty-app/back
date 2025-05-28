<?php

namespace App\Entity;

use App\Contract\HerdAwareInterface;
use App\Contract\OwnableInterface;
use App\Enum\QuantityUnit;
use App\Repository\FoodStockRepository;
use App\Trait\OwnableEntityTrait;
use App\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: FoodStockRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'foodStocks'),
])]
class FoodStock implements OwnableInterface, HerdAwareInterface
{
    use OwnableEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableTrait;

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
    #[Groups(['foodStock'])]
    #[Assert\Type(type: 'float')]
    private ?float $quantity = null;

    #[ORM\Column(enumType: QuantityUnit::class, nullable: false)]
    #[Groups(['foodStock'])]
    private ?QuantityUnit $quantityUnit = null;

    #[ORM\Column(length: 255)]
    #[Groups(['foodStock'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(
        max: 255,
    )]
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
