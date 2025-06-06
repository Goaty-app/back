<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableTrait;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['herd'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['herd'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, Herd>
     */
    #[ORM\OneToMany(targetEntity: Herd::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $herds;

    /**
     * @var Collection<int, Production>
     */
    #[ORM\OneToMany(targetEntity: Production::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $productions;

    /**
     * @var Collection<int, ProductionType>
     */
    #[ORM\OneToMany(targetEntity: ProductionType::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $productionTypes;

    /**
     * @var Collection<int, FoodStock>
     */
    #[ORM\OneToMany(targetEntity: FoodStock::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $foodStocks;

    /**
     * @var Collection<int, FoodStockHistory>
     */
    #[ORM\OneToMany(targetEntity: FoodStockHistory::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $foodStockHistories;

    /**
     * @var Collection<int, FoodStockType>
     */
    #[ORM\OneToMany(targetEntity: FoodStockType::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $foodStockTypes;

    /**
     * @var Collection<int, Animal>
     */
    #[ORM\OneToMany(targetEntity: Animal::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $animals;

    /**
     * @var Collection<int, AnimalType>
     */
    #[ORM\OneToMany(targetEntity: AnimalType::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $animalTypes;

    /**
     * @var Collection<int, Healthcare>
     */
    #[ORM\OneToMany(targetEntity: Healthcare::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $healthcares;

    /**
     * @var Collection<int, HealthcareType>
     */
    #[ORM\OneToMany(targetEntity: HealthcareType::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $healthcareTypes;

    /**
     * @var Collection<int, Breeding>
     */
    #[ORM\OneToMany(targetEntity: Breeding::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $breedings;

    /**
     * @var Collection<int, Birth>
     */
    #[ORM\OneToMany(targetEntity: Birth::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $births;

    /**
     * @var Collection<int, Media>
     */
    #[ORM\OneToMany(targetEntity: Media::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $medias;

    public function __construct()
    {
        $this->herds = new ArrayCollection();
        $this->productions = new ArrayCollection();
        $this->productionTypes = new ArrayCollection();
        $this->foodStocks = new ArrayCollection();
        $this->foodStockHistories = new ArrayCollection();
        $this->foodStockTypes = new ArrayCollection();
        $this->animals = new ArrayCollection();
        $this->animalTypes = new ArrayCollection();
        $this->healthcares = new ArrayCollection();
        $this->healthcareTypes = new ArrayCollection();
        $this->breedings = new ArrayCollection();
        $this->births = new ArrayCollection();
        $this->medias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Herd>
     */
    public function getHerds(): Collection
    {
        return $this->herds;
    }

    public function addHerd(Herd $herd): static
    {
        if (!$this->herds->contains($herd)) {
            $this->herds->add($herd);
            $herd->setOwner($this);
        }

        return $this;
    }

    public function removeHerd(Herd $herd): static
    {
        if ($this->herds->removeElement($herd)) {
            // set the owning side to null (unless already changed)
            if ($herd->getOwner() === $this) {
                $herd->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Animal>
     */
    public function getAnimals(): Collection
    {
        return $this->animals;
    }

    public function addAnimal(Animal $animal): static
    {
        if (!$this->animals->contains($animal)) {
            $this->animals->add($animal);
            $animal->setOwner($this);
        }

        return $this;
    }

    public function removeAnimal(Animal $animal): static
    {
        if ($this->animals->removeElement($animal)) {
            if ($animal->getOwner() === $this) {
                $animal->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AnimalType>
     */
    public function getAnimalTypes(): Collection
    {
        return $this->animalTypes;
    }

    public function addAnimalType(AnimalType $animalType): static
    {
        if (!$this->animalTypes->contains($animalType)) {
            $this->animalTypes->add($animalType);
            $animalType->setOwner($this);
        }

        return $this;
    }

    public function removeAnimalType(AnimalType $animalType): static
    {
        if ($this->animalTypes->removeElement($animalType)) {
            if ($animalType->getOwner() === $this) {
                $animalType->setOwner(null);
            }
        }

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
            $production->setOwner($this);
        }

        return $this;
    }

    public function removeProduction(Production $production): static
    {
        if ($this->productions->removeElement($production)) {
            // set the owning side to null (unless already changed)
            if ($production->getOwner() === $this) {
                $production->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductionType>
     */
    public function getProductionTypes(): Collection
    {
        return $this->productionTypes;
    }

    public function addProductionType(ProductionType $productionType): static
    {
        if (!$this->productionTypes->contains($productionType)) {
            $this->productionTypes->add($productionType);
            $productionType->setOwner($this);
        }

        return $this;
    }

    public function removeProductionType(ProductionType $productionType): static
    {
        if ($this->productionTypes->removeElement($productionType)) {
            // set the owning side to null (unless already changed)
            if ($productionType->getOwner() === $this) {
                $productionType->setOwner(null);
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
            $foodStock->setOwner($this);
        }

        return $this;
    }

    public function removeFoodStock(FoodStock $foodStock): static
    {
        if ($this->foodStocks->removeElement($foodStock)) {
            // set the owning side to null (unless already changed)
            if ($foodStock->getOwner() === $this) {
                $foodStock->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FoodStockHistory>
     */
    public function getFoodStockHistories(): Collection
    {
        return $this->foodStockHistories;
    }

    public function addFoodStockHistory(FoodStockHistory $foodStockHistory): static
    {
        if (!$this->foodStockHistories->contains($foodStockHistory)) {
            $this->foodStockHistories->add($foodStockHistory);
            $foodStockHistory->setOwner($this);
        }

        return $this;
    }

    public function removeFoodStockHistory(FoodStockHistory $foodStockHistory): static
    {
        if ($this->foodStockHistories->removeElement($foodStockHistory)) {
            // set the owning side to null (unless already changed)
            if ($foodStockHistory->getOwner() === $this) {
                $foodStockHistory->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FoodStockType>
     */
    public function getFoodStockTypes(): Collection
    {
        return $this->foodStockTypes;
    }

    public function addFoodStockType(FoodStockType $foodStockType): static
    {
        if (!$this->foodStockTypes->contains($foodStockType)) {
            $this->foodStockTypes->add($foodStockType);
            $foodStockType->setOwner($this);
        }

        return $this;
    }

    public function removeFoodStockType(FoodStockType $foodStockType): static
    {
        if ($this->foodStockTypes->removeElement($foodStockType)) {
            // set the owning side to null (unless already changed)
            if ($foodStockType->getOwner() === $this) {
                $foodStockType->setOwner(null);
            }
        }

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
            $healthcare->setOwner($this);
        }

        return $this;
    }

    public function removeHealthcare(Healthcare $healthcare): static
    {
        if ($this->healthcares->removeElement($healthcare)) {
            // set the owning side to null (unless already changed)
            if ($healthcare->getOwner() === $this) {
                $healthcare->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HealthcareType>
     */
    public function getHealthcareTypes(): Collection
    {
        return $this->healthcareTypes;
    }

    public function addHealthcareType(HealthcareType $healthcareType): static
    {
        if (!$this->healthcareTypes->contains($healthcareType)) {
            $this->healthcareTypes->add($healthcareType);
            $healthcareType->setOwner($this);
        }

        return $this;
    }

    public function removeHealthcareType(HealthcareType $healthcareType): static
    {
        if ($this->healthcareTypes->removeElement($healthcareType)) {
            // set the owning side to null (unless already changed)
            if ($healthcareType->getOwner() === $this) {
                $healthcareType->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Breeding>
     */
    public function getBreedings(): Collection
    {
        return $this->breedings;
    }

    public function addBreeding(Breeding $breeding): static
    {
        if (!$this->breedings->contains($breeding)) {
            $this->breedings->add($breeding);
            $breeding->setOwner($this);
        }

        return $this;
    }

    public function removeBreeding(Breeding $breeding): static
    {
        if ($this->breedings->removeElement($breeding)) {
            // set the owning side to null (unless already changed)
            if ($breeding->getOwner() === $this) {
                $breeding->setOwner(null);
            }
        }

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
            $birth->setOwner($this);
        }

        return $this;
    }

    public function removeBirth(Birth $birth): static
    {
        if ($this->births->removeElement($birth)) {
            // set the owning side to null (unless already changed)
            if ($birth->getOwner() === $this) {
                $birth->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Media>
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addMedia(Media $media): static
    {
        if (!$this->medias->contains($media)) {
            $this->medias->add($media);
            $media->setOwner($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): static
    {
        if ($this->medias->removeElement($media)) {
            // set the owning side to null (unless already changed)
            if ($media->getOwner() === $this) {
                $media->setOwner(null);
            }
        }

        return $this;
    }
}
