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
    #[Groups(['herd'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['herd'])]
    private ?string $name = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['herd'])]
    private ?string $location = null;

    #[ORM\Column]
    #[Groups(['herd'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'herd', targetEntity: Animal::class)]
    #[Groups(['herd'])]
    private Collection $animals;

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

    public function __construct()
    {
        $this->animals = new ArrayCollection();
    }

    public function getAnimals(): Collection
    {
        return $this->animals;
    }

    public function addAnimal(Animal $animal): static
    {
        if (!$this->animals->contains($animal)) {
            $this->animals[] = $animal;
            $animal->setHerd($this);
        }

        return $this;
    }

    public function removeAnimal(Animal $animal): static
    {
        if ($this->animals->removeElement($animal)) {
            if ($animal->getHerd() === $this) {
                $animal->setHerd(null);
            }
        }

        return $this;
    }
}
