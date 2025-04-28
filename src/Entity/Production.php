<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Interface\HasOwner;
use App\Entity\Trait\HasOwnerTrait;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Repository\ProductionRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

#[ORM\Entity(repositoryClass: ProductionRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: false)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(name: 'owner', inversedBy: 'productions'),
])]
class Production
{
    use SoftDeleteableEntity;
    use HasOwnerTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['production'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['production'])]
    private ?Herd $herd = null;

    public function getId(): ?int
    {
        return $this->id;
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
}
