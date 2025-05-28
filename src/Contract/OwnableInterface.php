<?php

namespace App\Contract;

use App\Entity\User;

interface OwnableInterface
{
    public function getOwner(): ?User;

    public function setOwner(?User $owner): static;
}
