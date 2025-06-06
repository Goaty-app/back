<?php

namespace App\Entity;

use App\Entity\Contract\OwnableInterface;
use App\Entity\Trait\OwnableEntityTrait;
use App\Entity\Trait\TimestampableTrait;
use App\Enum\BreedingStatus;
use App\Repository\BreedingRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: BreedingRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'breedings'),
])]
class Breeding implements OwnableInterface
{
    use OwnableEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['breeding', 'birth'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'femaleBreedings')]
    #[Groups(['breeding'])]
    private ?Animal $female = null;

    #[ORM\ManyToOne(inversedBy: 'maleBreedings')]
    #[Groups(['breeding'])]
    private ?Animal $male = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['breeding'])]
    #[Assert\DateTime(format: 'Y-m-d H:i:s', message: 'assert.datetime')]
    private ?DateTimeInterface $matingDateStart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['breeding'])]
    #[Assert\DateTime(format: 'Y-m-d H:i:s', message: 'assert.datetime')]
    private ?DateTimeInterface $matingDateEnd = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['breeding'])]
    #[Assert\PositiveOrZero]
    private ?int $expectedChildCount = null;

    #[ORM\Column(enumType: BreedingStatus::class, nullable: true)]
    #[Groups(['breeding'])]
    #[Assert\Choice(callback: [BreedingStatus::class, 'enumValues'], message: 'assert.choice')]
    private ?BreedingStatus $status = null;

    /**
     * @var Collection<int, Birth>
     */
    #[ORM\OneToMany(targetEntity: Birth::class, mappedBy: 'breeding', orphanRemoval: true)]
    private Collection $births;

    public function __construct()
    {
        $this->births = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFemale(): ?Animal
    {
        return $this->female;
    }

    public function setFemale(?Animal $female): static
    {
        $this->female = $female;

        return $this;
    }

    public function getMale(): ?Animal
    {
        return $this->male;
    }

    public function setMale(?Animal $male): static
    {
        $this->male = $male;

        return $this;
    }

    public function getMatingDateStart(): ?DateTimeInterface
    {
        return $this->matingDateStart;
    }

    public function setMatingDateStart(?DateTimeInterface $matingDateStart): static
    {
        $this->matingDateStart = $matingDateStart;

        return $this;
    }

    public function getMatingDateEnd(): ?DateTimeInterface
    {
        return $this->matingDateEnd;
    }

    public function setMatingDateEnd(?DateTimeInterface $matingDateEnd): static
    {
        $this->matingDateEnd = $matingDateEnd;

        return $this;
    }

    public function getExpectedChildCount(): ?int
    {
        return $this->expectedChildCount;
    }

    public function setExpectedChildCount(?int $expectedChildCount): static
    {
        $this->expectedChildCount = $expectedChildCount;

        return $this;
    }

    public function getStatus(): ?BreedingStatus
    {
        return $this->status;
    }

    public function setStatus(?BreedingStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Birth>
     */
    public function getBirths(): Collection
    {
        return $this->births;
    }

    public function addBirth(Birth $birth): static
    {
        if (!$this->births->contains($birth)) {
            $this->births->add($birth);
            $birth->setBreeding($this);
        }

        return $this;
    }

    public function removeBirth(Birth $birth): static
    {
        if ($this->births->removeElement($birth)) {
            // set the owning side to null (unless already changed)
            if ($birth->getBreeding() === $this) {
                $birth->setBreeding(null);
            }
        }

        return $this;
    }
}
