<?php

namespace App\Entity;

use App\Interface\HasOwner;
use App\Repository\BirthRepository;
use App\Trait\HasOwnerTrait;
use App\Traits\TimestampableTrait;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: BirthRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'births'),
])]
class Birth implements HasOwner
{
    use HasOwnerTrait;
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['birth', 'animal'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['birth'])]
    private ?DateTimeInterface $birthDate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['birth'])]
    private ?float $birthWeight = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['birth'])]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'births')]
    #[Groups(['birth'])]
    private ?Breeding $breeding = null;

    // Hack to make a ManyToOne like a OneToOne
    #[ORM\ManyToOne(inversedBy: 'birth')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['birth'])]
    private ?Animal $child = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBirthDate(): ?DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getBirthWeight(): ?float
    {
        return $this->birthWeight;
    }

    public function setBirthWeight(?float $birthWeight): static
    {
        $this->birthWeight = $birthWeight;

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

    public function getBreeding(): ?Breeding
    {
        return $this->breeding;
    }

    public function setBreeding(?Breeding $breeding): static
    {
        $this->breeding = $breeding;

        return $this;
    }

    // Hack to make a ManyToOne like a OneToOne
    public function getChild(): ?Animal
    {
        return $this->child;
    }

    // Hack to make a ManyToOne like a OneToOne
    public function setChild(Animal $child): static
    {
        $this->child = $child;

        return $this;
    }
}
