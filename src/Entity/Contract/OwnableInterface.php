<?php

namespace App\Entity\Contract;

use App\Entity\User;

interface OwnableInterface
{
    public function getOwner(): ?User;

    public function setOwner(?User $owner): static;
}
