<?php

namespace App\Entity;

use App\Entity\Contract\OwnableInterface;
use App\Entity\Trait\OwnableEntityTrait;
use App\Entity\Trait\TimestampableTrait;
use App\Enum\Operation;
use App\Repository\FoodStockHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: FoodStockHistoryRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'foodStockHistories'),
])]
class FoodStockHistory implements OwnableInterface
{
    use OwnableEntityTrait;
    use SoftDeleteableEntity;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['foodStockHistory'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['foodStockHistory'])]
    #[Assert\NotBlank()]
    #[Assert\PositiveOrZero]
    private ?float $quantity = null;

    #[ORM\Column(enumType: Operation::class, nullable: false)]
    #[Groups(['foodStockHistory'])]
    private ?Operation $operation = null;

    #[ORM\ManyToOne(inversedBy: 'foodStockHistories')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['foodStockHistory'])]
    private ?FoodStock $foodStock = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOperation(): ?Operation
    {
        return $this->operation;
    }

    public function setOperation(Operation $operation): static
    {
        $this->operation = $operation;

        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getFoodStock(): ?FoodStock
    {
        return $this->foodStock;
    }

    public function setFoodStock(?FoodStock $foodStock): static
    {
        $this->foodStock = $foodStock;

        return $this;
    }
}
