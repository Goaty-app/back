<?php

namespace App\Entity;

use App\Enum\Gender;
use App\Enum\Country;
use App\Entity\Interface\HasOwner;
use App\Entity\Trait\HasOwnerTrait;
use App\Repository\AnimalRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

#[ORM\Entity(repositoryClass: AnimalRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'animals'),
])]
class Animal implements HasOwner
{
    use HasOwnerTrait;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['animal','herd'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Herd::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['animal'])]
    private ?Herd $herd = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['animal'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['animal'])]
    private ?string $idNumber = null;

    #[ORM\Column(enumType: Gender::class, nullable: true)]
    #[Groups(['animal'])]
    private ?Gender $gender = null;

    #[ORM\Column(enumType: Country::class, nullable: true)]
    #[Groups(['animal'])]
    private ?Country $originCountry = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['animal'])]
    private ?string $behaviorNotes = null;

    #[ORM\Column(length: 255)]
    #[Groups(['animal'])]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIdNumber(): ?string
    {
        return $this->idNumber;
    }

    public function setIdNumber(string $idNumber): static
    {
        $this->idNumber = $idNumber;

        return $this;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(?Gender $gender): static
    {
        $this->gender = $gender;
        return $this;
    }

    public function getOriginCountry(): ?Country
    {
        return $this->originCountry;
    }

    public function setOriginCountry(?Country $originCountry): static
    {
        $this->originCountry = $originCountry;
        return $this;
    }

    public function getBehaviorNotes(): ?string
    {
        return $this->behaviorNotes;
    }

    public function setBehaviorNotes(?string $behaviorNotes): static
    {
        $this->behaviorNotes = $behaviorNotes;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
