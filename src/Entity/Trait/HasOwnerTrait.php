<?php

namespace App\Entity\Trait;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait HasOwnerTrait
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
