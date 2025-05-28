<?php

namespace App\Entity;

use App\Contract\HerdAwareInterface;
use App\Enum\Country;
use App\Enum\Gender;
use App\Interface\HasOwner;
use App\Repository\AnimalRepository;
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
#[ORM\Entity(repositoryClass: AnimalRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'animals'),
])]
class Animal implements HasOwner, HerdAwareInterface
{
    use OwnableEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['animal', 'healthcare', 'breeding', 'birth'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Herd::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['animal'])]
    private ?Herd $herd = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['animal'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(
        max: 50,
    )]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Groups(['animal'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(
        max: 50,
    )]
    private ?string $idNumber = null;

    #[ORM\Column(enumType: Gender::class, nullable: true)]
    #[Groups(['animal'])]
    private ?Gender $gender = null;

    #[ORM\Column(enumType: Country::class, nullable: true)]
    #[Groups(['animal'])]
    private ?Country $originCountry = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['animal'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(
        max: 255,
    )]
    private ?string $behaviorNotes = null;

    #[ORM\Column(length: 255)]
    #[Groups(['animal'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(
        max: 255,
    )]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'animals')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['animal'])]
    private ?AnimalType $animalType = null;

    // Hack to make a ManyToOne like a OneToOne
    /**
     * @var Collection<int, Birth>
     */
    #[ORM\OneToMany(targetEntity: Birth::class, mappedBy: 'child', orphanRemoval: true)]
    #[Groups(['animal'])]
    private Collection $birth;

    /**
     * @var Collection<int, Healthcare>
     */
    #[ORM\OneToMany(targetEntity: Healthcare::class, mappedBy: 'animal', orphanRemoval: true)]
    private Collection $healthcares;

    /**
     * @var Collection<int, Breeding>
     */
    #[ORM\OneToMany(targetEntity: Breeding::class, mappedBy: 'female', orphanRemoval: true)]
    private Collection $femaleBreedings;

    /**
     * @var Collection<int, Breeding>
     */
    #[ORM\OneToMany(targetEntity: Breeding::class, mappedBy: 'male', orphanRemoval: true)]
    private Collection $maleBreedings;

    public function __construct()
    {
        $this->healthcares = new ArrayCollection();
        $this->femaleBreedings = new ArrayCollection();
        $this->maleBreedings = new ArrayCollection();
    }

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

    public function getAnimalType(): ?AnimalType
    {
        return $this->animalType;
    }

    public function setAnimalType(?AnimalType $animalType): static
    {
        $this->animalType = $animalType;

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
            $healthcare->setAnimal($this);
        }

        return $this;
    }

    public function removeHealthcare(Healthcare $healthcare): static
    {
        if ($this->healthcares->removeElement($healthcare)) {
            // set the owning side to null (unless already changed)
            if ($healthcare->getAnimal() === $this) {
                $healthcare->setAnimal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Breeding>
     */
    public function getFemaleBreedings(): Collection
    {
        return $this->femaleBreedings;
    }

    public function addFemaleBreeding(Breeding $femaleBreeding): static
    {
        if (!$this->femaleBreedings->contains($femaleBreeding)) {
            $this->femaleBreedings->add($femaleBreeding);
            $femaleBreeding->setFemale($this);
        }

        return $this;
    }

    public function removeFemaleBreeding(Breeding $femaleBreeding): static
    {
        if ($this->femaleBreedings->removeElement($femaleBreeding)) {
            // set the owning side to null (unless already changed)
            if ($femaleBreeding->getFemale() === $this) {
                $femaleBreeding->setFemale(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Breeding>
     */
    public function getMaleBreedings(): Collection
    {
        return $this->maleBreedings;
    }

    public function addMaleBreeding(Breeding $maleBreeding): static
    {
        if (!$this->maleBreedings->contains($maleBreeding)) {
            $this->maleBreedings->add($maleBreeding);
            $maleBreeding->setMale($this);
        }

        return $this;
    }

    public function removeMaleBreeding(Breeding $maleBreeding): static
    {
        if ($this->maleBreedings->removeElement($maleBreeding)) {
            // set the owning side to null (unless already changed)
            if ($maleBreeding->getMale() === $this) {
                $maleBreeding->setMale(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Breeding>
     */
    public function getBreedings(): Collection
    {
        return new ArrayCollection([...$this->femaleBreedings, ...$this->maleBreedings]);
    }

    // Hack to make a ManyToOne like a OneToOne
    public function getBirth(): ?Birth
    {
        return $this->birth[0];
    }

    // Hack to make a ManyToOne like a OneToOne
    public function setBirth(Birth $birth): static
    {
        // set the owning side of the relation if necessary
        if ($birth->getChild() !== $this) {
            $birth->setChild($this);
            $this->birth = new Collection([$birth]);
        }

        return $this;
    }
}
