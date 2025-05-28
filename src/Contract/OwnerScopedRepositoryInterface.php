<?php

namespace App\Contract;

use App\Entity\User;

interface OwnerScopedRepositoryInterface
{
    public function findByOwner(User $user): array;

    public function findByOwnerFlex(string $column, int $value, User $user): array;

    public function findOneByIdAndOwner(int $id, User $user): ?object;
}
