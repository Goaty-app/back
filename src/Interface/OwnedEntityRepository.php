<?php

namespace App\Entity\Interface;

use App\Entity\User;

interface OwnedEntityRepository
{
    public function findByOwner(User $user): array;

    public function findByOwnerFlex(string $column, int $value, User $user): array;

    public function findOneByIdAndOwner(int $id, User $user): ?object;
}
