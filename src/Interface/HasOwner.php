<?php

namespace App\Entity\Interface;

use App\Entity\User;

interface HasOwner
{
    public function getOwner(): ?User;
}
