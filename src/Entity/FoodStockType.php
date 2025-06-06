<?php

namespace App\Entity;

use App\Entity\Contract\OwnableInterface;
use App\Entity\Trait\OwnableEntityTrait;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\FoodStockTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: FoodStockTypeRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'foodStockTypes'),
])]
class FoodStockType implements OwnableInterface
{
    use OwnableEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['foodStockType', 'foodStock'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['foodStockType', 'foodStock'])]
    #[Assert\NotBlank()]
    #[Assert\Length(
        max: 50,
    )]
    private ?string $name = null;

    /**
     * @var Collection<int, FoodStock>
     */
    #[ORM\OneToMany(targetEntity: FoodStock::class, mappedBy: 'foodStockType', orphanRemoval: true)]
    private Collection $foodStocks;

    public function __construct()
    {
        $this->foodStocks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, FoodStock>
     */
    public function getFoodStocks(): Collection
    {
        return $this->foodStocks;
    }

    public function addFoodStock(FoodStock $foodStock): static
    {
        if (!$this->foodStocks->contains($foodStock)) {
            $this->foodStocks->add($foodStock);
            $foodStock->setFoodStockType($this);
        }

        return $this;
    }

    public function removeFoodStock(FoodStock $foodStock): static
    {
        if ($this->foodStocks->removeElement($foodStock)) {
            // set the owning side to null (unless already changed)
            if ($foodStock->getFoodStockType() === $this) {
                $foodStock->setFoodStockType(null);
            }
        }

        return $this;
    }
}
