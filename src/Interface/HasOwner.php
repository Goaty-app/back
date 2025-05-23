<?php

namespace App\Interface;

use App\Entity\User;

interface HasOwner
{
    public function getOwner(): ?User;
}
