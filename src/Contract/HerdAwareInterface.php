<?php

namespace App\Contract;

use App\Entity\Herd;

interface HerdAwareInterface
{
    public function getHerd(): ?Herd;

    public function setHerd(?Herd $herd): static;
}
