<?php

namespace App\Interface;

use App\Entity\Herd;

interface HasHerd
{
    public function getHerd(): ?Herd;

    public function setHerd(?Herd $herd): static;
}
