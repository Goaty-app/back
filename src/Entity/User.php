<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
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
     * @var Collection<int, FoodStockType>
     */
    #[ORM\OneToMany(targetEntity: FoodStockType::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $foodStockTypes;

    public function __construct()
    {
        $this->herds = new ArrayCollection();
        $this->productions = new ArrayCollection();
        $this->productionTypes = new ArrayCollection();
        $this->foodStocks = new ArrayCollection();
        $this->foodStockTypes = new ArrayCollection();
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
}
