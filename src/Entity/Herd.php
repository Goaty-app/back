<?php

namespace App\Entity;

use App\Entity\Interface\HasOwner;
use App\Entity\Trait\HasOwnerTrait;
use App\Repository\HerdRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: HerdRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'herds'),
])]
class Herd implements HasOwner
{
    use HasOwnerTrait;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['herd', 'production', 'foodStock'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['herd', 'production', 'foodStock'])]
    private ?string $name = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['herd', 'production', 'foodStock'])]
    private ?string $location = null;

    #[ORM\Column]
    #[Groups(['herd', 'production', 'foodStock'])]
    private ?DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Production>
     */
    #[ORM\OneToMany(targetEntity: Production::class, mappedBy: 'herd', orphanRemoval: true)]
    private Collection $productions;

    /**
     * @var Collection<int, FoodStock>
     */
    #[ORM\OneToMany(targetEntity: FoodStock::class, mappedBy: 'herd', orphanRemoval: true)]
    private Collection $foodStocks;

    public function __construct()
    {
        $this->productions = new ArrayCollection();
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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

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

    /**
     * @return Collection<int, Production>
     */
    public function getProductions(): Collection
    {
        return $this->productions;
    }

    public function addProduction(Production $production): static
    {
        if (!$this->productions->contains($production)) {
            $this->productions->add($production);
            $production->setHerd($this);
        }

        return $this;
    }

    public function removeProduction(Production $production): static
    {
        if ($this->productions->removeElement($production)) {
            // set the owning side to null (unless already changed)
            if ($production->getHerd() === $this) {
                $production->setHerd(null);
            }
        }

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
            $foodStock->setHerd($this);
        }

        return $this;
    }

    public function removeFoodStock(FoodStock $foodStock): static
    {
        if ($this->foodStocks->removeElement($foodStock)) {
            // set the owning side to null (unless already changed)
            if ($foodStock->getHerd() === $this) {
                $foodStock->setHerd(null);
            }
        }

        return $this;
    }
}
