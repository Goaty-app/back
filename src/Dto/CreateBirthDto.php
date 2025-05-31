<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateBirthDto
{
    #[Assert\NotNull()]
    #[Assert\Positive()]
    public int $childId;

    #[Assert\Positive()]
    public ?int $breedingId = null;

    #[Assert\DateTime(format: 'Y-m-d H:i:s', message: 'assert.datetime')]
    public ?string $birthDate = null;

    #[Assert\PositiveOrZero]
    public ?float $birthWeight = null;

    #[Assert\Length(
        max: 255,
    )]
    public ?string $notes = null;
}
