<?php

namespace App\Entity;

use App\Entity\Contract\OwnableInterface;
use App\Entity\Trait\OwnableEntityTrait;
use App\Entity\Trait\TimestampableTrait;
use App\Repository\HealthcareRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: HealthcareRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'healthcares'),
])]
class Healthcare implements OwnableInterface
{
    use OwnableEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['healthcare'])]
    private ?int $id = null;

    // Animal assignment is immutable after creation
    #[ORM\ManyToOne(inversedBy: 'healthcares')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['healthcare'])]
    private ?Animal $animal = null;

    #[ORM\ManyToOne(inversedBy: 'healthcares')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['healthcare'])]
    private ?HealthcareType $healthcareType = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['healthcare'])]
    #[Assert\Length(
        max: 255,
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['healthcare'])]
    private ?DateTimeInterface $care_date = null;

    /**
     * @var Collection<int, Media>
     */
    #[ORM\OneToMany(mappedBy: 'healthcare', targetEntity: Media::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['uploadedAt' => 'DESC'])]
    #[Groups(['healthcare'])]
    private Collection $documents;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCareDate(): ?DateTimeInterface
    {
        return $this->care_date;
    }

    public function setCareDate(DateTimeInterface $care_date): static
    {
        $this->care_date = $care_date;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAnimal(): ?Animal
    {
        return $this->animal;
    }

    public function setAnimal(?Animal $animal): static
    {
        $this->animal = $animal;

        return $this;
    }

    public function getHealthcareType(): ?HealthcareType
    {
        return $this->healthcareType;
    }

    public function setHealthcareType(?HealthcareType $healthcareType): static
    {
        $this->healthcareType = $healthcareType;

        return $this;
    }

    /**
     * @return Collection<int, Media>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Media $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setHealthcare($this);
        }

        return $this;
    }

    public function removeDocument(Media $document): static
    {
        if ($this->documents->removeElement($document)) {
            if ($document->getHealthcare() === $this) {
                $document->setHealthcare(null);
            }
        }

        return $this;
    }
}
