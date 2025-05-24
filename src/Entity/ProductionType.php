<?php

namespace App\Entity;

use App\Interface\HasOwner;
use App\Repository\ProductionTypeRepository;
use App\Trait\HasOwnerTrait;
use App\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ProductionTypeRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'productionTypes'),
])]
class ProductionType implements HasOwner
{
    use HasOwnerTrait;
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['production', 'productionType'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['production', 'productionType'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Production>
     */
    #[ORM\OneToMany(targetEntity: Production::class, mappedBy: 'productionType', orphanRemoval: true)]
    private Collection $production;

    public function __construct()
    {
        $this->production = new ArrayCollection();
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
     * @return Collection<int, Production>
     */
    public function getProduction(): Collection
    {
        return $this->production;
    }

    public function addProduction(Production $production): static
    {
        if (!$this->production->contains($production)) {
            $this->production->add($production);
        }

        return $this;
    }

    public function removeProduction(Production $production): static
    {
        $this->production->removeElement($production);

        return $this;
    }
}
