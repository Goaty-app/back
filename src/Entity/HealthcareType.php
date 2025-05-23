<?php

namespace App\Entity;

use App\Interface\HasOwner;
use App\Repository\HealthcareTypeRepository;
use App\Trait\HasOwnerTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: HealthcareTypeRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'healthCareTypes'),
])]
class HealthcareType implements HasOwner
{
    use HasOwnerTrait;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['healthcareType', 'healthcare'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['healthcareType', 'healthcare'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Healthcare>
     */
    #[ORM\OneToMany(targetEntity: Healthcare::class, mappedBy: 'healthcareType', orphanRemoval: true)]
    private Collection $healthcares;

    public function __construct()
    {
        $this->healthcares = new ArrayCollection();
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
     * @return Collection<int, Healthcare>
     */
    public function getHealthcares(): Collection
    {
        return $this->healthcares;
    }

    public function addHealthcare(Healthcare $healthcare): static
    {
        if (!$this->healthcares->contains($healthcare)) {
            $this->healthcares->add($healthcare);
            $healthcare->setHealthcareType($this);
        }

        return $this;
    }

    public function removeHealthcare(Healthcare $healthcare): static
    {
        if ($this->healthcares->removeElement($healthcare)) {
            // set the owning side to null (unless already changed)
            if ($healthcare->getHealthcareType() === $this) {
                $healthcare->setHealthcareType(null);
            }
        }

        return $this;
    }
}
